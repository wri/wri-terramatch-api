<?php

namespace App\Validators;

class DraftValidator extends Validator
{
    public const CREATE = [
        'name' => 'present|string|between:1,255',
        'type' => 'present|string|in:pitch,offer,programme,site,site_submission,programme_submission,terrafund_programme,terrafund_nursery,terrafund_site,organisation,terrafund_nursery_submission,terrafund_site_submission,terrafund_programme_submission',
        'due_submission_id' => 'integer|exists:due_submissions,id',
        'terrafund_due_submission_id' => 'integer|exists:terrafund_due_submissions,id',
        'is_from_mobile' => 'boolean',
    ];

    public const MERGE = [
        'type' => 'present|string|in:site_submission,programme_submission,terrafund_nursery_submission,terrafund_site_submission,terrafund_programme_submission',
        'draft_ids' => 'present|array',
        'draft_ids.*' => 'exists:drafts,id',
    ];

    public const UPDATE_DATA_OFFER = [
        // offer
        'offer' => 'present|array|array_object',
        'offer.name' => 'present',
        'offer.description' => 'present',
        'offer.land_types' => 'present|array|array_array',
        'offer.land_types.*' => 'present',
        'offer.land_ownerships' => 'present|array|array_array',
        'offer.land_ownerships.*' => 'present',
        'offer.land_size' => 'present',
        'offer.land_continent' => 'present',
        'offer.land_country' => 'present',
        'offer.restoration_methods' => 'present|array|array_array',
        'offer.restoration_methods.*' => 'present',
        'offer.restoration_goals' => 'present|array|array_array',
        'offer.restoration_goals.*' => 'present',
        'offer.funding_sources' => 'present|array|array_array',
        'offer.funding_sources.*' => 'present',
        'offer.funding_amount' => 'present',
        'offer.funding_bracket' => 'present',
        'offer.price_per_tree' => 'present',
        'offer.long_term_engagement' => 'present',
        'offer.reporting_frequency' => 'present',
        'offer.reporting_level' => 'present',
        'offer.sustainable_development_goals' => 'present|array|array_array',
        'offer.sustainable_development_goals.*' => 'present',
        'offer.cover_photo' => 'present|nullable|integer|exists:uploads,id',
        'offer.video' => 'present|nullable|integer|exists:uploads,id',
        // offer document
        'offer_documents' => 'present|array|array_array',
        'offer_documents.*' => 'array|array_object',
        'offer_documents.*.name' => 'present',
        'offer_documents.*.type' => 'present',
        'offer_documents.*.document' => 'present|nullable|integer|exists:uploads,id',
        // offer contact
        'offer_contacts' => 'present|array|array_array',
        'offer_contacts.*' => 'array|array_object',
        'offer_contacts.*.team_member_id' => 'sometimes|present',
        'offer_contacts.*.user_id' => 'sometimes|present',
    ];

    public const UPDATE_DATA_PITCH = [
        // pitch
        'pitch' => 'present|array|array_object',
        'pitch.name' => 'present',
        'pitch.description' => 'present',
        'pitch.land_types' => 'present|array|array_array',
        'pitch.land_types.*' => 'present',
        'pitch.land_ownerships' => 'present|array|array_array',
        'pitch.land_ownerships.*' => 'present',
        'pitch.land_size' => 'present',
        'pitch.land_continent' => 'present',
        'pitch.land_country' => 'present',
        'pitch.land_geojson' => 'present',
        'pitch.restoration_methods' => 'present|array|array_array',
        'pitch.restoration_methods.*' => 'present',
        'pitch.restoration_goals' => 'present|array|array_array',
        'pitch.restoration_goals.*' => 'present',
        'pitch.funding_sources' => 'present|array|array_array',
        'pitch.funding_sources.*' => 'present',
        'pitch.funding_amount' => 'present',
        'pitch.funding_bracket' => 'present',
        'pitch.revenue_drivers' => 'present|array|array_array',
        'pitch.revenue_drivers.*' => 'present',
        'pitch.estimated_timespan' => 'present',
        'pitch.long_term_engagement' => 'present',
        'pitch.reporting_frequency' => 'present',
        'pitch.reporting_level' => 'present',
        'pitch.sustainable_development_goals' => 'present|array|array_array',
        'pitch.sustainable_development_goals.*' => 'present',
        'pitch.cover_photo' => 'present|nullable|integer|exists:uploads,id',
        'pitch.video' => 'present|nullable|integer|exists:uploads,id',
        'pitch.problem' => 'present',
        'pitch.anticipated_outcome' => 'present',
        'pitch.who_is_involved' => 'present',
        'pitch.local_community_involvement' => 'present',
        'pitch.training_involved' => 'present',
        'pitch.training_type' => 'present',
        'pitch.training_amount_people' => 'present',
        'pitch.people_working_in' => 'present',
        'pitch.people_amount_nearby' => 'present',
        'pitch.people_amount_abroad' => 'present',
        'pitch.people_amount_employees' => 'present',
        'pitch.people_amount_volunteers' => 'present',
        'pitch.benefited_people' => 'present',
        'pitch.future_maintenance' => 'present',
        'pitch.use_of_resources' => 'present',
        // pitch document
        'pitch_documents' => 'present|array|array_array',
        'pitch_documents.*' => 'array|array_object',
        'pitch_documents.*.name' => 'present',
        'pitch_documents.*.type' => 'present',
        'pitch_documents.*.document' => 'present|nullable|integer|exists:uploads,id',
        // pitch contact
        'pitch_contacts' => 'present|array|array_array',
        'pitch_contacts.*' => 'array|array_object',
        'pitch_contacts.*.team_member_id' => 'sometimes|present',
        'pitch_contacts.*.user_id' => 'sometimes|present',
        // carbon certification
        'carbon_certifications' => 'present|array|array_array',
        'carbon_certifications.*' => 'array|array_object',
        'carbon_certifications.*.type' => 'present',
        'carbon_certifications.*.other_value' => 'present',
        'carbon_certifications.*.link' => 'present',
        // restoration method metric
        'restoration_method_metrics' => 'present|array|array_array',
        'restoration_method_metrics.*' => 'array|array_object',
        'restoration_method_metrics.*.restoration_method' => 'present',
        'restoration_method_metrics.*.experience' => 'present',
        'restoration_method_metrics.*.land_size' => 'present',
        'restoration_method_metrics.*.price_per_hectare' => 'present',
        'restoration_method_metrics.*.biomass_per_hectare' => 'present',
        'restoration_method_metrics.*.carbon_impact' => 'present',
        'restoration_method_metrics.*.species_impacted' => 'present|array|array_array',
        'restoration_method_metrics.*.species_impacted.*' => 'present',
        // tree species
        'tree_species' => 'present|array|array_array',
        'tree_species.*' => 'array|array_object',
        'tree_species.*.name' => 'present',
        'tree_species.*.is_native' => 'present',
        'tree_species.*.count' => 'present',
        'tree_species.*.price_to_plant' => 'present',
        'tree_species.*.price_to_maintain' => 'present',
        'tree_species.*.saplings' => 'present',
        'tree_species.*.site_prep' => 'present',
        'tree_species.*.survival_rate' => 'present',
        'tree_species.*.produces_food' => 'present',
        'tree_species.*.produces_firewood' => 'present',
        'tree_species.*.produces_timber' => 'present',
        'tree_species.*.owner' => 'present',
        'tree_species.*.season' => 'present',
    ];

    public const UPDATE_DATA_PROGRAMME = [
        'programme' => 'present|array|array_object',
        'programme.name' => 'present',
        'programme.country' => 'present',
        'programme.continent' => 'present',
        'programme.end_date' => 'present',
        'programme.thumbnail' => 'sometimes',
        'boundary' => 'nullable',
        'boundary.boundary_geojson' => 'nullable',
        'programme_tree_species' => 'sometimes',
        'programme_tree_species_file' => 'sometimes',
        'additional_tree_species' => 'sometimes',
        'document_files' => 'sometimes|array',
        'aims' => 'present|array|array_object',
        'aims.year_five_trees' => 'present',
        'aims.restoration_hectares' => 'present',
        'aims.survival_rate' => 'sometimes',
        'aims.year_five_crown_cover' => 'present',
    ];

    public const UPDATE_DATA_SITE = [
        'site' => 'present|array|array_object',
        'site.programme_id' => 'present',
        'site.site_name' => 'present',
        'site.site_description' => 'present',
        'site.site_history' => 'sometimes',
        'site.end_date' => 'present',
        'site.planting_pattern' => 'present',
        'site.stratification_for_heterogeneity' => 'present',
        'boundary' => 'nullable',
        'boundary.boundary_geojson' => 'nullable',
        'narratives' => 'sometimes',
        'narratives.technical_narrative' => 'sometimes|nullable|string',
        'narratives.public_narrative' => 'sometimes|nullable|string',
        'aims' => 'present',
        'aims.aim_survival_rate' => 'sometimes',
        'aims.aim_year_five_crown_cover' => 'present',
        'aims.aim_direct_seeding_survival_rate' => 'sometimes',
        'aims.aim_natural_regeneration_trees_per_hectare' => 'sometimes',
        'aims.aim_natural_regeneration_hectares' => 'sometimes',
        'aims.aim_soil_condition' => 'sometimes',
        'aims.aim_number_of_mature_trees' => 'sometimes',
        'establishment_date' => 'present',
        'establishment_date.establishment_date' => 'present',
        'socioeconomic_benefits' => 'sometimes',
        'additional_tree_species' => 'sometimes',
        'document_files' => 'sometimes|array',
        'media' => 'present',
        'seeds' => 'present',
        'invasives' => 'present',
        'site_tree_species' => 'sometimes|present',
        'site_tree_species_file' => 'sometimes|present',
        'restoration_methods' => 'present|array',
        'restoration_methods.site_restoration_method_ids' => 'present|array',
        'land_tenure' => 'present|array',
        'progress' => 'present',
        'progress.invasives_skipped' => 'present',
    ];

    public const UPDATE_DATA_SITE_SUBMISSION = [
        'site_submission' => 'present|array|array_object',
        'site_submission.site_id' => 'present',
        'site_submission.created_by' => 'sometimes',
        'narratives' => 'sometimes',
        'narratives.technical_narrative' => 'sometimes|nullable|string',
        'narratives.public_narrative' => 'sometimes|nullable|string',
        'disturbances' => 'sometimes|array',
        'socioeconomic_benefits' => 'sometimes',
        'media' => 'present',
        'site_tree_species' => 'sometimes|present',
        'site_tree_species_file' => 'sometimes|present',
        'direct_seeding' => 'sometimes|present',
        'direct_seeding.direct_seeding_kg' => 'sometimes|present',
        'direct_seeding.kg_by_species' => 'sometimes|present',
        'disturbance_information' => 'sometimes|present',
        'workdays_paid' => 'sometimes|nullable|integer|max:99999',
        'workdays_volunteer' => 'sometimes|nullable|integer|max:99999',
        'additional_tree_species' => 'sometimes',
        'document_files' => 'sometimes|array',
        'progress' => 'present',
        'progress.jobs_and_livelihoods_skipped' => 'present',
        'progress.trees_planted_skipped' => 'present',
        'progress.disturbances_skipped' => 'present',
        'progress.direct_seeding_skipped' => 'present',
    ];

    public const UPDATE_DATA_PROGRAMME_SUBMISSION = [
        'programme_submission' => 'present|array|array_object',
        'programme_submission.programme_id' => 'present',
        'programme_submission.title' => 'present',
        'programme_submission.created_by' => 'sometimes',
        'socioeconomic_benefits' => 'sometimes',
        'narratives' => 'present',
        'narratives.technical_narrative' => 'present',
        'narratives.public_narrative' => 'sometimes|nullable|string',
        'programme_tree_species' => 'sometimes|present',
        'programme_tree_species_file' => 'sometimes|present',
        'workdays_paid' => 'sometimes|nullable|integer|max:99999',
        'workdays_volunteer' => 'sometimes|nullable|integer|max:99999',
        'additional_tree_species' => 'sometimes',
        'document_files' => 'sometimes|present',
        'media' => 'sometimes|present',
        'progress' => 'present',
        'progress.jobs_and_livelihoods_skipped' => 'present',
        'progress.trees_planted_skipped' => 'present',
    ];

    public const UPDATE_DATA_TERRAFUND_PROGRAMME = [
        'terrafund_programme' => 'present|array|array_object',
        'terrafund_programme.name' => 'present',
        'terrafund_programme.description' => 'present',
        'terrafund_programme.planting_start_date' => 'present',
        'terrafund_programme.planting_end_date' => 'present',
        'terrafund_programme.budget' => 'present',
        'terrafund_programme.status' => 'present',
        'terrafund_programme.project_country' => 'present',
        'terrafund_programme.home_country' => 'present',
        'terrafund_programme.boundary_geojson' => 'present',
        'terrafund_programme.history' => 'present',
        'terrafund_programme.objectives' => 'present',
        'terrafund_programme.environmental_goals' => 'present',
        'terrafund_programme.socioeconomic_goals' => 'present',
        'terrafund_programme.sdgs_impacted' => 'present',
        'terrafund_programme.long_term_growth' => 'present',
        'terrafund_programme.community_incentives' => 'present',
        'terrafund_programme.total_hectares_restored' => 'present',
        'terrafund_programme.trees_planted' => 'present',
        'terrafund_programme.jobs_created' => 'present',
        'tree_species' => 'sometimes|present',
        'tree_species_csv' => 'sometimes|present',
        'additional_files' => 'sometimes|present',
    ];

    public const UPDATE_DATA_TERRAFUND_NURSERY = [
        'terrafund_nursery' => 'present|array|array_object',
        'terrafund_nursery.name' => 'present',
        'terrafund_nursery.start_date' => 'present',
        'terrafund_nursery.end_date' => 'present',
        'terrafund_nursery.terrafund_programme_id' => 'present',
        'terrafund_nursery.seedling_grown' => 'present',
        'terrafund_nursery.planting_contribution' => 'present',
        'terrafund_nursery.nursery_type' => 'present',
        'tree_species' => 'sometimes|present',
        'tree_species_csv' => 'sometimes|present',
        'photos' => 'sometimes|present',
    ];

    public const UPDATE_DATA_TERRAFUND_SITE = [
        'terrafund_site' => 'present|array|array_object',
        'terrafund_site.name' => 'present',
        'terrafund_site.start_date' => 'present',
        'terrafund_site.end_date' => 'present',
        'terrafund_site.boundary_geojson' => 'present',
        'terrafund_site.terrafund_programme_id' => 'present',
        'terrafund_site.restoration_methods' => 'present|array',
        'terrafund_site.land_tenures' => 'present|array',
        'terrafund_site.hectares_to_restore' => 'present',
        'terrafund_site.landscape_community_contribution' => 'present',
        'terrafund_site.disturbances' => 'present',
        'photos' => 'sometimes|present',
    ];

    public const UPDATE_DATA_ORGANISATION = [
        'organisation' => 'present|array|array_object',
        'organisation.name' => 'sometimes|present',
        'organisation.description' => 'sometimes|present',
        'organisation.address_1' => 'sometimes|present',
        'organisation.address_2' => 'sometimes|present',
        'organisation.city' => 'sometimes|present',
        'organisation.state' => 'sometimes|present',
        'organisation.zip_code' => 'sometimes|present',
        'organisation.country' => 'sometimes|present',
        'organisation.phone_number' => 'sometimes|present',
        'organisation.full_time_permanent_employees' => 'sometimes|present',
        'organisation.seasonal_employees' => 'sometimes|present',
        'organisation.part_time_permanent_employees' => 'sometimes|present',
        'organisation.percentage_female' => 'sometimes|present',
        'organisation.percentage_youth' => 'sometimes|present',
        'organisation.website' => 'sometimes|present',
        'organisation.key_contact' => 'sometimes|present',
        'organisation.type' => 'sometimes|present',
        'organisation.account_type' => 'sometimes|present',
        'organisation.category' => 'sometimes|present',
        'organisation.facebook' => 'sometimes|present',
        'organisation.twitter' => 'sometimes|present',
        'organisation.linkedin' => 'sometimes|present',
        'organisation.instagram' => 'sometimes|present',
        'organisation.avatar' => 'sometimes|present',
        'organisation.cover_photo' => 'sometimes|present',
        'organisation.video' => 'sometimes|present',
        'organisation.founded_at' => 'sometimes|present',
        'organisation.community_engagement_strategy' => 'sometimes|present',
        'organisation.three_year_community_engagement' => 'sometimes|present',
        'organisation.women_farmer_engagement' => 'sometimes|present',
        'organisation.young_people_engagement' => 'sometimes|present',
        'organisation.monitoring_and_evaluation_experience' => 'sometimes|present',
        'organisation.community_follow_up' => 'sometimes|present',
        'organisation.total_hectares_restored' => 'sometimes|present',
        'organisation.hectares_restored_three_years' => 'sometimes|present',
        'organisation.total_trees_grown' => 'sometimes|present',
        'organisation.tree_survival_rate' => 'sometimes|present',
        'organisation.tree_maintenance_and_aftercare' => 'sometimes|present',
        'organisation.revenues_19' => 'sometimes|present',
        'organisation.revenues_20' => 'sometimes|present',
        'organisation.revenues_21' => 'sometimes|present',
        'files' => 'sometimes|present',
        'photos' => 'sometimes|present',
    ];

    public const UPDATE_DATA_TERRAFUND_NURSERY_SUBMISSION = [
        'terrafund_nursery_submission' => 'present|array|array_object',
        'terrafund_nursery_submission.seedlings_young_trees' => 'present',
        'terrafund_nursery_submission.interesting_facts' => 'present',
        'terrafund_nursery_submission.site_prep' => 'present',
        'terrafund_nursery_submission.shared_drive_link' => 'present',
        'terrafund_nursery_submission.terrafund_nursery_id' => 'present',
        'photos' => 'sometimes|present',
    ];

    public const UPDATE_DATA_TERRAFUND_SITE_SUBMISSION = [
        'terrafund_site_submission' => 'present|array|array_object',
        'terrafund_site_submission.terrafund_site_id' => 'present',
        'terrafund_site_submission.shared_drive_link' => 'present',
        'photos' => 'sometimes|present',
        'tree_species' => 'sometimes|present',
        'tree_species_csv' => 'sometimes|present',
        'non_tree_species' => 'sometimes|present',
        'disturbances' => 'sometimes|present',
    ];

    public const UPDATE_DATA_TERRAFUND_PROGRAMME_SUBMISSION = [
        'terrafund_programme_submission' => 'present|array|array_object',
        'terrafund_programme_submission.terrafund_programme_id' => 'present',
        'terrafund_programme_submission.shared_drive_link' => 'present',
        'terrafund_programme_submission.landscape_community_contribution' => 'present',
        'terrafund_programme_submission.top_three_successes' => 'present',
        'terrafund_programme_submission.maintenance_and_monitoring_activities' => 'present',
        'terrafund_programme_submission.significant_change' => 'present',
        'terrafund_programme_submission.percentage_survival_to_date' => 'present',
        'terrafund_programme_submission.survival_calculation' => 'present',
        'terrafund_programme_submission.survival_comparison' => 'present',
        'terrafund_programme_submission.ft_women' => 'present',
        'terrafund_programme_submission.ft_men' => 'present',
        'terrafund_programme_submission.ft_youth' => 'present',
        'terrafund_programme_submission.ft_total' => 'present',
        'terrafund_programme_submission.pt_women' => 'present',
        'terrafund_programme_submission.pt_men' => 'present',
        'terrafund_programme_submission.pt_youth' => 'present',
        'terrafund_programme_submission.pt_total' => 'present',
        'terrafund_programme_submission.people_annual_income_increased' => 'present',
        'terrafund_programme_submission.people_knowledge_skills_increased' => 'present',
        'terrafund_programme_submission.challenges_faced' => 'sometimes|present',
        'terrafund_programme_submission.challenges_and_lessons' => 'sometimes|present',
        'terrafund_programme_submission.lessons_learned' => 'sometimes|present',
        'terrafund_programme_submission.planted_trees' => 'sometimes|present',
        'terrafund_programme_submission.new_jobs_created' => 'sometimes|present',
        'terrafund_programme_submission.new_jobs_description' => 'sometimes|present',
        'terrafund_programme_submission.new_volunteers' => 'sometimes|present',
        'terrafund_programme_submission.volunteers_work_description' => 'sometimes|present',
        'terrafund_programme_submission.full_time_jobs_35plus' => 'sometimes|present',
        'terrafund_programme_submission.part_time_jobs_35plus' => 'sometimes|present',
        'terrafund_programme_submission.volunteer_35plus' => 'sometimes|present',
        'terrafund_programme_submission.beneficiaries' => 'sometimes|present',
        'terrafund_programme_submission.beneficiaries_description' => 'sometimes|present',
        'terrafund_programme_submission.women_beneficiaries' => 'sometimes|present',
        'terrafund_programme_submission.men_beneficiaries' => 'sometimes|present',
        'terrafund_programme_submission.beneficiaries_35plus' => 'sometimes|present',
        'terrafund_programme_submission.youth_beneficiaries' => 'sometimes|present',
        'terrafund_programme_submission.smallholder_beneficiaries' => 'sometimes|present',
        'terrafund_programme_submission.large_scale_beneficiaries' => 'sometimes|present',
        'terrafund_programme_submission.beneficiaries_income_increase' => 'sometimes|present',
        'terrafund_programme_submission.income_increase_description' => 'sometimes|present',
        'terrafund_programme_submission.beneficiaries_skills_knowledge_increase' => 'sometimes|present',
        'terrafund_programme_submission.skills_knowledge_description' => 'sometimes|present',
        'photos' => 'sometimes|present',
        'other_additional_documents' => 'sometimes|present',
        'terrafund_programme_submission.volunteer_women' => 'sometimes|present',
        'terrafund_programme_submission.volunteer_men' => 'sometimes|present',
        'terrafund_programme_submission.volunteer_smallholder_farmers' => 'sometimes|present',
        'terrafund_programme_submission.volunteer_youth' => 'sometimes|present',
        'terrafund_programme_submission.volunteer_total' => 'sometimes|present',
        'survival_rate_skipped' => 'sometimes|nullable|boolean',
        'jobs_skipped' => 'sometimes|nullable|boolean',
        'volunteers_skipped' => 'sometimes|nullable|boolean',
        'beneficiaries_skipped' => 'sometimes|nullable|boolean',
    ];
}
