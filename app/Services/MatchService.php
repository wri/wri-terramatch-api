<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * This class acts as a dumping ground for all the hand written SQL. Doing the
 * below using Eloquent was slow and difficult. By dropping down into hand
 * written SQL we can get exactly what we want in fewer queries without slaying
 * the database server.
 */
class MatchService
{
    public function findMatchingInterests(): array
    {
        return DB::select(
            "
                SELECT *
                FROM (
                    SELECT 
                        GROUP_CONCAT(`id`) AS `ids`, 
                        MD5(CONCAT(`offer_id`, ',', `pitch_id`)) AS `hash`, 
                        COUNT(`id`) AS `count`
                    FROM `interests`
                    WHERE `matched` = 0
                    GROUP BY `hash`
                ) AS `pre_matches`
                WHERE `count` >= 2
            "
        );
    }

    public function findMatches(): array
    {
        return DB::select(
            "
                SELECT
                   `matches`.`id`, 
                   IF(
                       `primary_interests`.`initiator` = 'offer', 
                       `primary_interests`.`id`, 
                       `secondary_interests`.`id`
                   ) AS `offer_interest_id`,
                   `primary_interests`.`offer_id`,
                   IF(
                       `primary_interests`.`initiator` = 'pitch', 
                       `primary_interests`.`id`, 
                       `secondary_interests`.`id`
                   ) AS `pitch_interest_id`,
                   `secondary_interests`.`pitch_id`,
                   `matches`.`created_at`
                FROM `matches`
                LEFT JOIN `interests` AS `primary_interests` 
                   ON `matches`.`primary_interest_id` = `primary_interests`.`id`
                LEFT JOIN `interests` AS `secondary_interests` 
                   ON `matches`.`secondary_interest_id` = `secondary_interests`.`id`
           "
        );
    }

    public function findMatchesByOrganisation(int $organisationId): array
    {
        return DB::select(
            "
                SELECT
                   `matches`.`id`, 
                   IF(
                       `primary_interests`.`initiator` = 'offer', 
                       `primary_interests`.`id`, 
                       `secondary_interests`.`id`
                   ) AS `offer_interest_id`,
                   `primary_interests`.`offer_id`,
                   IF(
                       `primary_interests`.`initiator` = 'pitch', 
                       `primary_interests`.`id`, 
                       `secondary_interests`.`id`
                   ) AS `pitch_interest_id`,
                   `secondary_interests`.`pitch_id`,
                   `matches`.`created_at`
                FROM `matches`
                LEFT JOIN `interests` AS `primary_interests` 
                   ON `matches`.`primary_interest_id` = `primary_interests`.`id`
                LEFT JOIN `interests` AS `secondary_interests` 
                   ON `matches`.`secondary_interest_id` = `secondary_interests`.`id`
                WHERE `primary_interests`.`organisation_id` = ?
                OR `secondary_interests`.`organisation_id` = ?
            ",
            [$organisationId, $organisationId]
        );
    }

    public function findMatch($id): object
    {
        return DB::select(
            "
                SELECT
                   `matches`.`id`, 
                   IF(
                       `primary_interests`.`initiator` = 'offer', 
                       `primary_interests`.`id`, 
                       `secondary_interests`.`id`
                   ) AS `offer_interest_id`,
                   `primary_interests`.`offer_id`,
                   IF(
                       `primary_interests`.`initiator` = 'pitch', 
                       `primary_interests`.`id`, 
                       `secondary_interests`.`id`
                   ) AS `pitch_interest_id`,
                   `secondary_interests`.`pitch_id`,
                   `matches`.`created_at`
                FROM `matches`
                LEFT JOIN `interests` AS `primary_interests` 
                   ON `matches`.`primary_interest_id` = `primary_interests`.`id`
                LEFT JOIN `interests` AS `secondary_interests` 
                   ON `matches`.`secondary_interest_id` = `secondary_interests`.`id`
                WHERE `matches`.`id` = ?
            ",
            [$id]
        )[0];
    }
    
    public function findOfferContacts(int $offerId): array
    {
        return DB::select(
            "
                SELECT
                    `users`.`first_name`,
                    `users`.`last_name`,
                    `users`.`email_address`,
                    `users`.`phone_number`,
                    `users`.`avatar`
                FROM offer_contacts AS user_offer_contacts
                LEFT JOIN users 
                    ON user_offer_contacts.user_id = users.id
                WHERE user_offer_contacts.offer_id = ?
                AND user_offer_contacts.user_id IS NOT NULL
                UNION 
                SELECT
                    `team_members`.`first_name`,
                    `team_members`.`last_name`,
                    `team_members`.`email_address`,
                    `team_members`.`phone_number`,
                    `team_members`.`avatar`
                FROM offer_contacts AS team_member_offer_contacts
                LEFT JOIN team_members 
                    ON team_member_offer_contacts.team_member_id = team_members.id
                WHERE team_member_offer_contacts.offer_id = ?
                AND team_member_offer_contacts.team_member_id IS NOT NULL
            ",
            [$offerId, $offerId]
        );
    }
    
    public function findPitchContacts(int $pitchId): array
    {
        return DB::select(
            "
                SELECT
                    `users`.`first_name`,
                    `users`.`last_name`,
                    `users`.`email_address`,
                    `users`.`phone_number`,
                    `users`.`avatar`
                FROM pitch_contacts AS user_pitch_contacts
                LEFT JOIN users 
                    ON user_pitch_contacts.user_id = users.id
                WHERE user_pitch_contacts.pitch_id = ?
                AND user_pitch_contacts.user_id IS NOT NULL
                UNION 
                SELECT
                    `team_members`.`first_name`,
                    `team_members`.`last_name`,
                    `team_members`.`email_address`,
                    `team_members`.`phone_number`,
                    `team_members`.`avatar`
                FROM pitch_contacts AS team_member_pitch_contacts
                LEFT JOIN team_members 
                    ON team_member_pitch_contacts.team_member_id = team_members.id
                WHERE team_member_pitch_contacts.pitch_id = ?
                AND team_member_pitch_contacts.team_member_id IS NOT NULL
            ",
            [$pitchId, $pitchId]
        );
    }

    public function assertInterestsMatch(object $primaryInterest, object $secondaryInterest): void
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