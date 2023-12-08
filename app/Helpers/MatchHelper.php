<?php

namespace App\Helpers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * This class acts as a dumping ground for all the hand written SQL. Doing the
 * below using Eloquent was slow and difficult. By dropping down into hand
 * written SQL we can get exactly what we want in fewer queries without slaying
 * the database server.
 */
class MatchHelper
{
    private function __construct()
    {
    }

    public static function findMatchingInterests(): array
    {
        return DB::select(
            "SELECT *
            FROM (
                SELECT 
                    GROUP_CONCAT(`id`) AS `ids`, 
                    MD5(CONCAT(`offer_id`, ',', `pitch_id`)) AS `hash`, 
                    COUNT(`id`) AS `count`
                FROM `interests`
                WHERE `has_matched` = 0
                GROUP BY `hash`
            ) AS `pre_matches`
            WHERE `count` >= 2;"
        );
    }

    public static function findMatches(): array
    {
        return DB::select(
            "SELECT
               `matches`.`id`, 
               IF(
                   `primary_interests`.`initiator` = 'offer', 
                   `primary_interests`.`id`, 
                   `secondary_interests`.`id`
               ) AS `offer_interest_id`,
               `primary_interests`.`offer_id`,
               `offers`.`name` AS `offer_name`,
               IF(
                   `primary_interests`.`initiator` = 'pitch', 
                   `primary_interests`.`id`, 
                   `secondary_interests`.`id`
               ) AS `pitch_interest_id`,
               `secondary_interests`.`pitch_id`,
               `pitch_versions`.`name` AS `pitch_name`,
               `monitorings`.`id` as `monitoring_id`,
               `matches`.`created_at`
            FROM `matches`
            LEFT JOIN `interests` AS `primary_interests` 
                ON `matches`.`primary_interest_id` = `primary_interests`.`id`
            LEFT JOIN `interests` AS `secondary_interests` 
                ON `matches`.`secondary_interest_id` = `secondary_interests`.`id`
            LEFT JOIN `offers`
                ON `primary_interests`.`offer_id` = `offers`.`id`
            LEFT JOIN `pitches`
                ON `secondary_interests`.`pitch_id` = `pitches`.`id`
            LEFT JOIN `pitch_versions`
                ON `pitch_versions`.`pitch_id` = `pitches`.`id`
                AND `pitch_versions`.`status` = 'approved'
            LEFT JOIN `monitorings` 
                ON `matches`.`id` = `monitorings`.`match_id`
            WHERE `offers`.`visibility` NOT IN ('archived', 'finished')
            AND `pitches`.`visibility` NOT IN ('archived', 'finished')
            ORDER BY `matches`.`created_at` DESC;"
        );
    }

    public static function findMatchesByOrganisation(int $organisationId): array
    {
        return DB::select(
            "SELECT
               `matches`.`id`, 
               IF(
                   `primary_interests`.`initiator` = 'offer', 
                   `primary_interests`.`id`, 
                   `secondary_interests`.`id`
               ) AS `offer_interest_id`,
               `primary_interests`.`offer_id`,
               `offers`.`name` AS `offer_name`,
               IF(
                   `primary_interests`.`initiator` = 'pitch', 
                   `primary_interests`.`id`, 
                   `secondary_interests`.`id`
               ) AS `pitch_interest_id`,
               `secondary_interests`.`pitch_id`,
               `pitch_versions`.`name` AS `pitch_name`,
               `monitorings`.`id` as `monitoring_id`,
               `matches`.`created_at`
            FROM `matches`
            LEFT JOIN `interests` AS `primary_interests` 
                ON `matches`.`primary_interest_id` = `primary_interests`.`id`
            LEFT JOIN `interests` AS `secondary_interests` 
                ON `matches`.`secondary_interest_id` = `secondary_interests`.`id`
            LEFT JOIN `offers`
                ON `primary_interests`.`offer_id` = `offers`.`id`
            LEFT JOIN `pitches`
                ON `secondary_interests`.`pitch_id` = `pitches`.`id`
            LEFT JOIN `pitch_versions`
                ON `pitch_versions`.`pitch_id` = `pitches`.`id`
                AND `pitch_versions`.`status` = 'approved'
            LEFT JOIN `monitorings` 
                ON `matches`.`id` = `monitorings`.`match_id`
            WHERE (`primary_interests`.`organisation_id` = ? OR `secondary_interests`.`organisation_id` = ?)
            AND `offers`.`visibility` NOT IN ('archived', 'finished')
            AND `pitches`.`visibility` NOT IN ('archived', 'finished')
            ORDER BY `matches`.`created_at` DESC;",
            [$organisationId, $organisationId]
        );
    }

    public static function findMatch($id): object
    {
        $rows = DB::select(
            "SELECT
               `matches`.`id`, 
               IF(
                   `primary_interests`.`initiator` = 'offer', 
                   `primary_interests`.`id`, 
                   `secondary_interests`.`id`
               ) AS `offer_interest_id`,
               `primary_interests`.`offer_id`,
               `offers`.`name` AS `offer_name`,
               IF(
                   `primary_interests`.`initiator` = 'pitch', 
                   `primary_interests`.`id`, 
                   `secondary_interests`.`id`
               ) AS `pitch_interest_id`,
               `secondary_interests`.`pitch_id`,
               `pitch_versions`.`name` AS `pitch_name`,
               `monitorings`.`id` as `monitoring_id`,
               `matches`.`created_at`
            FROM `matches`
            LEFT JOIN `interests` AS `primary_interests` 
                ON `matches`.`primary_interest_id` = `primary_interests`.`id`
            LEFT JOIN `interests` AS `secondary_interests` 
                ON `matches`.`secondary_interest_id` = `secondary_interests`.`id`
            LEFT JOIN `offers`
                ON `primary_interests`.`offer_id` = `offers`.`id`
            LEFT JOIN `pitches`
                ON `secondary_interests`.`pitch_id` = `pitches`.`id`
            LEFT JOIN `pitch_versions`
                ON `pitch_versions`.`pitch_id` = `pitches`.`id`
                AND `pitch_versions`.`status` = 'approved'
            LEFT JOIN `monitorings` 
                ON `matches`.`id` = `monitorings`.`match_id`
            WHERE `matches`.`id` = ?
            AND `offers`.`visibility` NOT IN ('archived', 'finished')
            AND `pitches`.`visibility` NOT IN ('archived', 'finished');",
            [$id]
        );
        if (count($rows) < 1) {
            throw new ModelNotFoundException();
        }

        return $rows[0];
    }

    public static function findOfferContacts(int $offerId): array
    {
        $offerId = (int) $offerId;

        return DB::select(
            "SELECT * FROM (
                SELECT
                    `users`.`id`,
                    'user' AS `model`,
                    `user_offer_contacts`.`offer_id`,
                    `users`.`first_name`,
                    `users`.`last_name`,
                    `users`.`email_address`,
                    `users`.`phone_number`,
                    `users`.`avatar`,
                    `users`.`job_role`,
                    `users`.`facebook`,
                    `users`.`twitter`,
                    `users`.`linkedin`,
                    `users`.`instagram`
                FROM offer_contacts AS user_offer_contacts
                LEFT JOIN users 
                    ON user_offer_contacts.user_id = users.id
                WHERE user_offer_contacts.offer_id = ?
                AND user_offer_contacts.user_id IS NOT NULL
                UNION 
                SELECT
                    `team_members`.`id`,
                    'team_member' AS `model`,
                    `team_member_offer_contacts`.`offer_id`,
                    `team_members`.`first_name`,
                    `team_members`.`last_name`,
                    `team_members`.`email_address`,
                    `team_members`.`phone_number`,
                    `team_members`.`avatar`,
                    `team_members`.`job_role`,
                    `team_members`.`facebook`,
                    `team_members`.`twitter`,
                    `team_members`.`linkedin`,
                    `team_members`.`instagram`
                FROM offer_contacts AS team_member_offer_contacts
                LEFT JOIN team_members 
                    ON team_member_offer_contacts.team_member_id = team_members.id
                WHERE team_member_offer_contacts.offer_id = ?
                AND team_member_offer_contacts.team_member_id IS NOT NULL
            ) AS contacts
            ORDER BY last_name DESC, first_name DESC;",
            [$offerId, $offerId]
        );
    }

    public static function findPitchContacts(int $pitchId): array
    {
        $pitchId = (int) $pitchId;

        return DB::select(
            "SELECT * FROM (
                SELECT
                    `users`.`id`,
                    'user' AS `model`,
                    `user_pitch_contacts`.`pitch_id`,
                    `users`.`first_name`,
                    `users`.`last_name`,
                    `users`.`email_address`,
                    `users`.`phone_number`,
                    `users`.`avatar`,
                    `users`.`job_role`,
                    `users`.`facebook`,
                    `users`.`twitter`,
                    `users`.`linkedin`,
                    `users`.`instagram`
                FROM pitch_contacts AS user_pitch_contacts
                LEFT JOIN users 
                    ON user_pitch_contacts.user_id = users.id
                WHERE user_pitch_contacts.pitch_id = ?
                AND user_pitch_contacts.user_id IS NOT NULL
                UNION 
                SELECT
                    `team_members`.`id`,
                    'team_member' AS `model`,
                    `team_member_pitch_contacts`.`pitch_id`,
                    `team_members`.`first_name`,
                    `team_members`.`last_name`,
                    `team_members`.`email_address`,
                    `team_members`.`phone_number`,
                    `team_members`.`avatar`,
                    `team_members`.`job_role`,
                    `team_members`.`facebook`,
                    `team_members`.`twitter`,
                    `team_members`.`linkedin`,
                    `team_members`.`instagram`
                FROM pitch_contacts AS team_member_pitch_contacts
                LEFT JOIN team_members 
                    ON team_member_pitch_contacts.team_member_id = team_members.id
                WHERE team_member_pitch_contacts.pitch_id = ?
                AND team_member_pitch_contacts.team_member_id IS NOT NULL
            ) AS contacts
            ORDER BY last_name DESC, first_name DESC;",
            [$pitchId, $pitchId]
        );
    }

    public static function assertInterestsMatch(object $primaryInterest, object $secondaryInterest): void
    {
        if ($primaryInterest->offer_id != $secondaryInterest->offer_id) {
            throw new Exception();
        }
        if ($primaryInterest->pitch_id != $secondaryInterest->pitch_id) {
            throw new Exception();
        }
        if ($primaryInterest->initiator == $secondaryInterest->initiator) {
            throw new Exception();
        }
    }
}
