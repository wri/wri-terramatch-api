<?php

namespace App\Models\V2\Demographics;

class DemographicCollections
{
    public const PAID_NURSERY_OPERATIONS = 'paid-nursery-operations';
    public const PAID_PROJECT_MANAGEMENT = 'paid-project-management';
    public const PAID_OTHER = 'paid-other-activities';
    public const VOLUNTEER_NURSERY_OPERATIONS = 'volunteer-nursery-operations';
    public const VOLUNTEER_PROJECT_MANAGEMENT = 'volunteer-project-management';
    public const VOLUNTEER_OTHER = 'volunteer-other-activities';
    public const DIRECT = 'direct';
    public const CONVERGENCE = 'convergence';
    public const PAID_SITE_ESTABLISHMENT = 'paid-site-establishment';
    public const PAID_PLANTING = 'paid-planting';
    public const PAID_SITE_MAINTENANCE = 'paid-site-maintenance';
    public const PAID_SITE_MONITORING = 'paid-site-monitoring';
    public const VOLUNTEER_SITE_ESTABLISHMENT = 'volunteer-site-establishment';
    public const VOLUNTEER_PLANTING = 'volunteer-planting';
    public const VOLUNTEER_SITE_MAINTENANCE = 'volunteer-site-maintenance';
    public const VOLUNTEER_SITE_MONITORING = 'volunteer-site-monitoring';

    public const WORKDAYS_PROJECT_COLLECTIONS = [
        self::PAID_NURSERY_OPERATIONS => 'Paid Nursery Operations',
        self::PAID_PROJECT_MANAGEMENT => 'Paid Project Management',
        self::PAID_OTHER => 'Paid Other Activities',
        self::VOLUNTEER_NURSERY_OPERATIONS => 'Volunteer Nursery Operations',
        self::VOLUNTEER_PROJECT_MANAGEMENT => 'Volunteer Project Management',
        self::VOLUNTEER_OTHER => 'Volunteer Other Activities',
        self::DIRECT => 'Direct Workdays',
        self::CONVERGENCE => 'Convergence Workdays',
    ];

    public const WORKDAYS_SITE_COLLECTIONS = [
        self::PAID_SITE_ESTABLISHMENT => 'Paid Site Establishment',
        self::PAID_PLANTING => 'Paid Planting',
        self::PAID_SITE_MAINTENANCE => 'Paid Site Maintenance',
        self::PAID_SITE_MONITORING => 'Paid Site Monitoring',
        self::PAID_OTHER => 'Paid Other Activities',
        self::VOLUNTEER_SITE_ESTABLISHMENT => 'Volunteer Site Establishment',
        self::VOLUNTEER_PLANTING => 'Volunteer Planting',
        self::VOLUNTEER_SITE_MAINTENANCE => 'Volunteer Site Maintenance',
        self::VOLUNTEER_SITE_MONITORING => 'Volunteer Site Monitoring',
        self::VOLUNTEER_OTHER => 'Volunteer Other Activities',
    ];

    public const DIRECT_INCOME = 'direct-income';
    public const INDIRECT_INCOME = 'indirect-income';
    public const DIRECT_BENEFITS = 'direct-benefits';
    public const INDIRECT_BENEFITS = 'indirect-benefits';
    public const DIRECT_CONSERVATION_PAYMENTS = 'direct-conservation-payments';
    public const INDIRECT_CONSERVATION_PAYMENTS = 'indirect-conservation-payments';
    public const DIRECT_MARKET_ACCESS = 'direct-market-access';
    public const INDIRECT_MARKET_ACCESS = 'indirect-market-access';
    public const DIRECT_CAPACITY = 'direct-capacity';
    public const INDIRECT_CAPACITY = 'indirect-capacity';
    public const DIRECT_TRAINING = 'direct-training';
    public const INDIRECT_TRAINING = 'indirect-training';
    public const DIRECT_LAND_TITLE = 'direct-land-title';
    public const INDIRECT_LAND_TITLE = 'indirect-land-title';
    public const DIRECT_LIVELIHOODS = 'direct-livelihoods';
    public const INDIRECT_LIVELIHOODS = 'indirect-livelihoods';
    public const DIRECT_PRODUCTIVITY = 'direct-productivity';
    public const INDIRECT_PRODUCTIVITY = 'indirect-productivity';
    public const DIRECT_OTHER = 'direct-other';
    public const INDIRECT_OTHER = 'indirect-other';

    public const RESTORATION_PARTNERS_PROJECT_COLLECTIONS = [
        self::DIRECT_INCOME => 'Direct Income',
        self::INDIRECT_INCOME => 'Indirect Income',
        self::DIRECT_BENEFITS => 'Direct In-kind Benefits',
        self::INDIRECT_BENEFITS => 'Indirect In-kind Benefits',
        self::DIRECT_CONSERVATION_PAYMENTS => 'Direct Conservation Agreement Payments',
        self::INDIRECT_CONSERVATION_PAYMENTS => 'Indirect Conservation Agreement Payments',
        self::DIRECT_MARKET_ACCESS => 'Direct Increased Market Access',
        self::INDIRECT_MARKET_ACCESS => 'Indirect Increased Market Access',
        self::DIRECT_CAPACITY => 'Direct Increased Capacity',
        self::INDIRECT_CAPACITY => 'Indirect Increased Capacity',
        self::DIRECT_TRAINING => 'Direct Training',
        self::INDIRECT_TRAINING => 'Indirect Training',
        self::DIRECT_LAND_TITLE => 'Direct Newly Secured Land Title',
        self::INDIRECT_LAND_TITLE => 'Indirect Newly Secured Land Title',
        self::DIRECT_LIVELIHOODS => 'Direct Traditional Livelihoods or Customer Rights',
        self::INDIRECT_LIVELIHOODS => 'Indirect Traditional Livelihoods or Customer Rights',
        self::DIRECT_PRODUCTIVITY => 'Direct Increased Productivity',
        self::INDIRECT_PRODUCTIVITY => 'Indirect Increased Productivity',
        self::DIRECT_OTHER => 'Direct Other',
        self::INDIRECT_OTHER => 'Indirect Other',
    ];

    public const FULL_TIME = 'full-time';
    public const PART_TIME = 'part-time';

    public const JOBS_PROJECT_COLLECTIONS = [
        self::FULL_TIME => 'Full-time',
        self::PART_TIME => 'Part-time',
    ];

    public const VOLUNTEER = 'volunteer';

    public const VOLUNTEERS_PROJECT_COLLECTIONS = [
        self::VOLUNTEER => 'Volunteer',
    ];

    public const ALL = 'all';
    public const TRAINING = 'training';

    public const BENEFICIARIES_PROJECT_COLLECTIONS = [
        self::ALL => 'All Beneficiaries',
        self::TRAINING => 'Training Beneficiaries',
    ];
}
