<?php

namespace App\Services\Search;

use Exception;
use Illuminate\Database\Eloquent\Builder;

trait SearchScopeTrait
{
    public function scopeSearch(Builder $query, Conditions $conditions, Int $organisationId = null): Builder
    {
        /**
         * This section ensures that the search scope is only used for the
         * pitch_versions and offers models. Nothing else should be attempting
         * to use this!
         */
        if (! in_array($this->getTable(), ['pitch_versions', 'offers'])) {
            throw new Exception();
        }
        /**
         * This section omits archived, fully funded, or finished pitches and
         * offers from the search results. It also (optionally) omits your
         * organisation's pitches and offers.
         */
        switch ($this->getTable()) {
            case 'pitch_versions':
                $query->leftJoin('pitches', 'pitch_versions.pitch_id', '=', 'pitches.id');
                $query->whereNotIn('pitches.visibility', ['archived', 'fully_invested_funded', 'finished']);
                if (! is_null($organisationId)) {
                    $query->where('pitches.organisation_id', '!=', $organisationId);
                }

                break;
            case 'offers':
                $query->whereNotIn('visibility', ['archived', 'fully_invested_funded', 'finished']);
                if (! is_null($organisationId)) {
                    $query->where('organisation_id', '!=', $organisationId);
                }

                break;
            default:
                throw new Exception();
        }
        /**
         * This section calculates the compatibility score. The filters that are
         * passed in aren't actually treated as filters; they're treated more
         * like preferences. From these preferences we can calculate a
         * compatibility score to sort by. This means that you will be shown
         * results in searched that don't match your filters... this is normal
         * behaviour. If we didn't do this every single result would have a 100
         * compatibility score!
         */
        if (count($conditions->where) > 0) {
            $ifs = [];
            $bindings = [];
            foreach ($conditions->where as $where) {
                /**
                 * This section is vulnerable to SQL injection attacks if the
                 * App\Services\Search\SearchService class doesn't validate the filters. By the
                 * nature of PDO, column names can't be escaped... this means we have to
                 * rely on a whitelist of valid column names. This is delegated to the
                 * SearchService.
                 */
                switch ($where[1]) {
                    case 'contains':
                        $logic = [];
                        $logic[] = '`' . $where[0] . "` = '[]'";
                        foreach ($where[2] as $value) {
                            $logic[] = 'JSON_CONTAINS(`' . $where[0] . '`, ?)';
                            $bindings[] = '"' . $value . '"';
                        }
                        $ifs[] = 'IF(' . implode(' OR ', $logic) . ', 1, 0)';

                        break;
                    case 'in':
                        $logic = [];
                        $logic[] = '`' . $where[0] . '` IS NULL';
                        foreach ($where[2] as $value) {
                            $logic[] = '`' . $where[0] . '` = ?';
                            $bindings[] = $value;
                        }
                        $ifs[] = 'IF(' . implode(' OR ', $logic) . ', 1, 0)';

                        break;
                    case 'between':
                        $ifs[] = 'IF(`' . $where[0] . '` IS NULL OR `' . $where[0] . '` BETWEEN ? AND ?, 1, 0)';
                        $bindings[] = $where[2][0];
                        $bindings[] = $where[2][1];

                        break;
                    case 'boolean':
                        $ifs[] = 'IF(`' . $where[0] . '` IS NULL OR `' . $where[0] . '` = ?, 1, 0)';
                        $bindings[] = $where[2] == 'true' ? 1 : 0;

                        break;
                    default:
                        throw new Exception();
                }
            }
            /**
             * This section works by dividing 100 by the number of filters provided
             * and then multiplying that by the number of successfully fulfilled
             * filters. Each filter is reduced to an IF expression which returns an
             * integer of 1 or 0 depending on whether the filter is met.
             */
            $sql = 'ROUND((100 / ?) * (' . implode(' + ', $ifs) . ')) AS `compatibility_score`';
            array_unshift($bindings, count($conditions->where));
            $query->selectRaw('*, ' . $sql, $bindings);
        } else {
            $query->selectRaw("*, '100' AS `compatibility_score`");
        }
        /**
         * This section deals with ordering the results. We can be sneaky here
         * and avoid doing a costly JOIN by converting created_at to id. By the
         * very nature of incrementing IDs, the order returned by created_at and
         * id will be the same.
         */
        if ($conditions->orderColumn == 'created_at') {
            switch ($this->getTable()) {
                case 'pitch_versions':
                    $conditions->orderColumn = 'pitch_id';

                    break;
                case 'offers':
                    $conditions->orderColumn = 'id';

                    break;
                default:
                    throw new Exception();
            }
        }
        $query->orderBy($conditions->orderColumn, $conditions->orderDirection);
        /**
         * This section deals with paginating the results.
         */
        $query->offset($conditions->offset);
        $query->limit($conditions->limit);

        /**
         * This section returns the query builder object. Without it everything
         * here is ignored.
         */
        return $query;
    }
}
