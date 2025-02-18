<?php

namespace Database\Seeders;

use App\Helpers\I18nHelper;
use App\Models\V2\LocalizationKey;
use Illuminate\Database\Seeder;

class LocalizationKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createLocalizationKey('application-submitted-confirmation.subject', 'Your Application Has Been Submitted');
        $this->createLocalizationKey('application-submitted-confirmation.title', 'Your Application Has Been Submitted!');

        // form-submission-approved
        $this->createLocalizationKey('form-submission-approved.subject', 'Application Approved');
        $this->createLocalizationKey('form-submission-approved.title', 'Your application has been approved');
        $this->createLocalizationKey('form-submission-approved.body', 'Your application has been approved.');
        $this->createLocalizationKey('form-submission-approved.body-feedback', 'Your application has been approved. Please see comments below:<br><br>');

        // form-submission-rejected
        $this->createLocalizationKey('form-submission-rejected.subject', 'Application Status Update');
        $this->createLocalizationKey('form-submission-rejected.title', 'THANK YOU FOR YOUR APPLICATION');
        $this->createLocalizationKey('form-submission-rejected.body', 'After careful review, our team has decided your application will not move forward.');
        $this->createLocalizationKey('form-submission-rejected.body-feedback', 'After careful review, our team has decided your application will not move forward. Please see the comments below for more details or any follow-up resources.<br><br> {feedback}');

        // entity-status-change
        $this->createLocalizationKey('entity-status-change.subject-approved', 'Your {entityTypeName} Has Been Approved');
        $this->createLocalizationKey('entity-status-change.subject-needs-more-information', 'There is More Information Requested About Your {entityTypeName}');
        $this->createLocalizationKey('entity-status-change.body-report-approved', 'Thank you for submitting your {parentEntityName} report.' .
            '<br><br>The information has been reviewed by your project manager and has been approved. <br><br>{feedback}' .
            '<br><br>If you have any additional questions please reach out to your project manager or to info@terramatch.org<br><br>');
        $this->createLocalizationKey('entity-status-change.body-report-needs-more-information', 'Thank you for submitting your {parentEntityName} report.' .
            '<br><br>The information has been reviewed by your project manager and they would like to see the following updates: <br><br> {feedback}' .
            '<br><br>If you have any additional questions please reach out to your project manager or to info@terramatch.org<br><br>');
        $this->createLocalizationKey('entity-status-change.body-entity-approved', 'Thank you for submitting your {lowerEntityTypeName} information for {entityName}.' .
            '<br><br>The information has been reviewed by your project manager and has been approved. <br><br>{feedback}' .
            '<br><br>If you have any additional questions please reach out to your project manager or to info@terramatch.org<br><br>');
        $this->createLocalizationKey('entity-status-change.body-entity-needs-more-information', 'Thank you for submitting your {lowerEntityTypeName} information for {entityName}.' .
            '<br><br>The information has been reviewed by your project manager and they would like to see the following updates: <br><br> {feedback}' .
            '<br><br>If you have any additional questions please reach out to your project manager or to info@terramatch.org<br><br>');
        $this->createLocalizationKey('entity-status-change.cta', 'View {entityTypeName}');
        //

        // form-submission-feedback-received
        $this->createLocalizationKey('form-submission-feedback-received.subject', 'You have received feedback on your application');
        $this->createLocalizationKey('form-submission-feedback-received.title', 'You have received feedback on your application');
        $this->createLocalizationKey('form-submission-feedback-received.body', 'Your application requires more information.');
        $this->createLocalizationKey('form-submission-feedback-received.body-feedback', 'Your application requires more information. Please see comments below:<br><br> {feedback}');

        // form-submission-final-stage-approved
        $this->createLocalizationKey('form-submission-final-stage-approved.subject', 'Application Approved');
        $this->createLocalizationKey('form-submission-final-stage-approved.title', 'Your application has been approved');
        $this->createLocalizationKey('form-submission-final-stage-approved.body', 'Your application has successfully passed all stages of our evaluation process and has been officially approved.
            If you have any immediate queries, please do not hesitate to reach out to our support team.');
        $this->createLocalizationKey('form-submission-final-stage-approved.body-feedback', 'Your application has successfully passed all stages of our evaluation process and has been officially approved. Please see the comments below:<br><br> {feedback}' .
            '<br><br>If you have any immediate queries, please do not hesitate to reach out to our dedicated support team.');

        // form-submission-submitted
        $this->createLocalizationKey('form-submission-submitted.subject', 'You have submitted an application');
        $this->createLocalizationKey('form-submission-submitted.title', 'You have submitted an application');
        $this->createLocalizationKey('form-submission-submitted.body', 'Your application has been submitted');

        // interest-shown
        $this->createLocalizationKey('interest-shown.subject', 'Someone Has Shown Interest In One Of Your Projects');
        $this->createLocalizationKey('interest-shown.title', 'Someone Has Shown Interest In One Of Your Projects');
        $this->createLocalizationKey('interest-shown.body', '{name} has shown interest in one of your projects.<br><br>' .
            'Follow this link to view their project.');
        $this->createLocalizationKey('interest-shown.cta', 'View Project');

        // match-mail
        $this->createLocalizationKey('match-mail.subject-admin', 'Match Detected');
        $this->createLocalizationKey('match-mail.title-admin', 'Match Detected');
        $this->createLocalizationKey('match-mail.body-admin', '{firstName} and {secondName} have matched.<br><br>Follow this link to view the match.');
        $this->createLocalizationKey('match-mail.cta-admin', 'View Match');

        $this->createLocalizationKey('match-mail.subject-funder', 'Someone Has Matched With One Of Your Funding Offers');
        $this->createLocalizationKey('match-mail.title-funder', 'Someone Has Matched With One Of Your Funding Offers');
        $this->createLocalizationKey('match-mail.body-funder', 'Congratulations! {firstName} has matched with one of your funding offers.<br><br>' .
            'Follow the link below to view their contact details.<br><br>' .
            'If you have decided to move forward together, we encourage you to monitor your project on TerraMatch. Our monitoring system allows you to set mutually agreed targets, easily report on project progress using our templates, and access WRI\'s state-of-the-art satellite monitoring so that you can track progress over the long term.<br><br>' .
            'Check out the monitoring section at <a href="https://www.TerraMatch.org" style="color: #000000;">TerraMatch.org</a>.');
        $this->createLocalizationKey('match-mail.subject-user', 'Someone Has Matched With One Of Your Projects');
        $this->createLocalizationKey('match-mail.title-user', 'Someone Has Matched With One Of Your Projects');
        $this->createLocalizationKey('match-mail.body-user', 'Congratulations! {firstName} has matched with one of your projects.<br><br>' .
            'Follow the link below to view their contact details.<br><br>' .
            'If you have decided to move forward together, we encourage you to monitor your project on TerraMatch. Our monitoring system allows you to set mutually agreed targets, easily report on project progress using our templates, and access WRI\'s state-of-the-art satellite monitoring to show the funder that your trees are surviving.<br><br>' .
            'Check out the monitoring section at <a href="https://www.TerraMatch.org" style="color: #000000;">TerraMatch.org</a>.');
        $this->createLocalizationKey('match-mail.cta', 'View Contact Details');

        // organisation-approved
        $this->createLocalizationKey('organisation-approved.subject', 'Your organization has been accepted into TerraMatch.');
        $this->createLocalizationKey('organisation-approved.title', 'YOUR ORGANIZATION HAS BEEN ACCEPTED INTO TERRAMATCH.');
        $this->createLocalizationKey('organisation-approved.body', 'Please login to submit an application or report on a monitored project. If you have any questions, please reach out to info@terramatch.org');
        $this->createLocalizationKey('organisation-approved.cta', 'LOGIN');

        // organisation-rejected
        $this->createLocalizationKey('organisation-rejected.subject', 'Your organization has been rejected from joining TerraMatch.');
        $this->createLocalizationKey('organisation-rejected.title', 'Your organization has been rejected from joining TerraMatch.');
        $this->createLocalizationKey('organisation-rejected.body', 'This could be due to the fact that your organization is already on TerraMatch, 
            your organization will not benefit from the services that TerraMatch provides 
            or we do not have enough information to understand what your organization does. 
            Please login to TerraMatch to view a more detail description about why your 
            organization request has been rejected.');

        // organisation-submit-confirmation
        $this->createLocalizationKey('organisation-submit-confirmation.subject', 'Organization request submitted to TerraMatch.');
        $this->createLocalizationKey('organisation-submit-confirmation.title', 'ORGANIZATION REQUEST SUBMITTED TO TERRAMATCH.');
        $this->createLocalizationKey('organisation-submit-confirmation.body', 'Your organization has been submitted and is in review with WRI. You can continue to use the platform to whilst your ' .
            'application is in review. If you have any questions, feel free to message us at info@terramatch.org.<br><br>' .
            '<br><br>--<br>' .
            'Votre organisation a été soumise et est en cours d\'examen par le WRI. Vous pouvez continuer à utiliser la plateforme pendant que ' .
            'votre demande est en cours d\'examen. Si vous avez des questions, n\'hésitez pas à nous envoyer un message à info@terramatch.org.');

        // organisation-user-approved
        $this->createLocalizationKey('organisation-user-approved.subject', 'You have been accepted to join {organisationName} on TerraMatch');
        $this->createLocalizationKey('organisation-user-approved.title', 'You have been accepted to join {organisationName} on TerraMatch');
        $this->createLocalizationKey('organisation-user-approved.body', 'You have been accepted to join {organisationName} on TerraMatch. Log-in to view or update your organization’s information.');

        // organisation-user-join-requested
        $this->createLocalizationKey('organisation-user-join-requested.subject', 'A user has requested to join your organization');
        $this->createLocalizationKey('organisation-user-join-requested.title', 'A user has requested to join your organization');
        $this->createLocalizationKey('organisation-user-join-requested.body', 'A user has requested to join your organization. Please go to the ‘Meet the Team’ page to review this request.');

        // organisation-user-rejected
        $this->createLocalizationKey('organisation-user-rejected.subject', 'Your request to join {organisationName} on TerraMatch has been rejected');
        $this->createLocalizationKey('organisation-user-rejected.title', 'Your request to join {organisationName} on TerraMatch has been rejected');
        $this->createLocalizationKey('organisation-user-rejected.body', 'Your request to join {organisationName} on TerraMatch has been rejected. <br><br>' .
            'Please set-up a new organizational profile on TerraMatch if you wish to join the platform. ' .
            'Please reach out the help center here if you need more information: <a href="https://terramatchsupport.zendesk.com/hc/en-us/requests/new">https://terramatchsupport.zendesk.com/hc/en-us/requests/new</a>');

        // progress-update-created
        $this->createLocalizationKey('progress-update-created.subject', 'Report Received');
        $this->createLocalizationKey('progress-update-created.title', 'Report Received');
        $this->createLocalizationKey('progress-update-created.body', 'A new progress update report has been submitted for {pitchName}.<br><br>' .
            'Click below to view the report.');
        $this->createLocalizationKey('progress-update-created.cta', 'View Report');

        // project-invite-received
        $this->createLocalizationKey('project-invite-received.subject', 'Project Invite');
        $this->createLocalizationKey('project-invite-received.title', 'Project Invite');
        $this->createLocalizationKey('project-invite-received.body', 'You have been sent an invite to join {name}.<br><br>' .
            'Click below to accept the invite.<br><br>');
        $this->createLocalizationKey('project-invite-received.cta', 'Accept invite');

        // project-updated
        $this->createLocalizationKey('project-updated.subject-match', 'A Project You\'ve Matched With Has Changed');
        $this->createLocalizationKey('project-updated.title-match', 'A Project You\'ve Matched With Has Changed');
        $this->createLocalizationKey('project-updated.subject-interest', 'A Project You\'re Interested In Has Changed');
        $this->createLocalizationKey('project-updated.title-interest', 'A Project You\'re Interested In Has Changed');
        $this->createLocalizationKey('project-updated.body', '{name} has changed.<br><br>' .
            'You may want to review the changes to ensure you\'re still interested.<br><br>' .
            'Follow this link to view the project.');
        $this->createLocalizationKey('project-updated.cta', 'View Project');

        // reset-password
        $this->createLocalizationKey('reset-password.subject', 'RESET YOUR PASSWORD');
        $this->createLocalizationKey('reset-password.title', 'RESET YOUR PASSWORD');
        $this->createLocalizationKey('reset-password.body', 'You\'ve requested a password reset.<br><br>' .
            'Follow this link to reset your password. It\'s valid for 2 hours.<br><br>' .
            'If you have any questions, feel free to message us at info@terramatch.org.');
        $this->createLocalizationKey('reset-password.cta', 'Reset Password');

        // send-login-details
        $this->createLocalizationKey('send-login-details.subject', 'Welcome to TerraMatch!');
        $this->createLocalizationKey('send-login-details.title', 'Welcome to TerraMatch 🌱 !');
        $this->createLocalizationKey('send-login-details.body', 'Hi {userName},<br><br>' .
            'We\'re thrilled to let you know that your access to TerraMatch is now active!<br><br>' .
            'Your user email used for your account is {mail}<br><br>' .
            'Please click on the button below to set your new password. This link is valid for 7 days from the day you received this email.<br><br>' .
            'If you have any questions or require assistance, our support team is ready to help at info@terramatch.org or +44 7456 289369 (WhatsApp only).<br><br>'.
            'We look forward to working with you!<br><br>' .
            '<br><br>' .
            'Best regards,<br><br>' .
            'TerraMatch Support');
        $this->createLocalizationKey('send-login-details.cta', 'Set Password');

        // polygon-operations-complete
        $this->createLocalizationKey('polygon-validation.subject', 'Your TerraMatch Polygon {operation} is Complete');
        $this->createLocalizationKey('polygon-validation.title', 'YOUR POLYGON {operationUpper} IS COMPLETE');
        $this->createLocalizationKey('polygon-validation.body', 'Your {operation} for Site {siteName} completed at {completedTime} GMT.');
        $this->createLocalizationKey('polygon-validation.cta', 'OPEN SITE');

        // satellite-map-created
        $this->createLocalizationKey('satellite-map-created.subject', 'Remote Sensing Map Received');
        $this->createLocalizationKey('satellite-map-created.title', 'Remote Sensing Map Received');
        $this->createLocalizationKey('satellite-map-created.body', 'WRI has submitted an updated remote sensing map for {name}.<br><br>' .
            'Click below to view the map.');
        $this->createLocalizationKey('satellite-map-created.cta', 'View Monitored Project');

        // target-accepted
        $this->createLocalizationKey('target-accepted.subject', 'Monitoring Targets Approved');
        $this->createLocalizationKey('target-accepted.title', 'Monitoring Targets Approved');
        $this->createLocalizationKey('target-accepted.body', 'Monitoring targets have been approved for {name}.<br><br>' .
            'Click below to view the newly unlocked project dashboard.');
        $this->createLocalizationKey('target-accepted.cta', 'View Monitored Project');

        // target-created
        $this->createLocalizationKey('target-created.subject', 'Monitoring Targets Set');
        $this->createLocalizationKey('target-created.title', 'Monitoring Targets Set');
        $this->createLocalizationKey('target-created.body', 'You have been sent monitoring targets to approve for {name}.<br><br>' .
            'Click below to view these targets.<br><br>' .
            'Note: you may need to update your funding status on TerraMatch to view.');
        $this->createLocalizationKey('target-created.cta', 'View Monitoring Terms');

        // target-updated
        $this->createLocalizationKey('target-updated.subject', 'Monitoring Targets Need Review');
        $this->createLocalizationKey('target-updated.title', 'Monitoring Targets Need Review');
        $this->createLocalizationKey('target-updated.body', 'Monitoring targets for {name} have been edited and need reviewing.<br><br>' .
            'Click below to review the edited targets.');
        $this->createLocalizationKey('target-updated.cta', 'View Monitoring Terms');

        // terrafund-programme-submission-received
        $this->createLocalizationKey('terrafund-programme-submission-received.subject', 'Terrafund Programme Report Submitted');
        $this->createLocalizationKey('terrafund-programme-submission-received.title', 'Terrafund Programme Report Submitted');
        $this->createLocalizationKey('terrafund-programme-submission-received.body', 'A new report has been submitted!<br><br>' .
            '{name} has a new report submited.<br><br>' .
            'Click below to view and edit the report.<br><br>');
        $this->createLocalizationKey('terrafund-programme-submission-received.cta', 'View Report');

        // terrafund-report-reminder
        $this->createLocalizationKey('terrafund-report-reminder.subject', 'Terrafund Report Reminder');
        $this->createLocalizationKey('terrafund-report-reminder.title', 'YOU HAVE A REPORT DUE!');
        $this->createLocalizationKey('terrafund-report-reminder.body', 'Your next report is due on July 31. It should reflect any progress made between January 1, 2023 and June 30, 2022.<br><br>' .
            'If you have any questions, feel free to message us at info@terramatch.org.' .
            '<br><br>---<br><br>' .
            'Votre prochain rapport doit être remis le 31 juillet. Il doit refléter tous les progrès réalisés entre le 1er janvier 2023 et le 30 juin 2023. ');
        $this->createLocalizationKey('terrafund-report-reminder.cta', 'View Project');

        // terrafund-site-and-nursery-reminder
        $this->createLocalizationKey('terrafund-site-and-nursery-reminder.subject', 'Terrafund Site & Nursery Reminder');
        $this->createLocalizationKey('terrafund-site-and-nursery-reminder.title', 'Terrafund Site & Nursery Reminder');
        $this->createLocalizationKey('terrafund-site-and-nursery-reminder.body', 'You haven\'t created any sites or nurseries for your project, reports are due in a month.<br><br>' .
            'Click below to create.<br><br>');
        $this->createLocalizationKey('terrafund-site-and-nursery-reminder.cta', 'Create a site or nursery');

        // unmatch
        $this->createLocalizationKey('unmatch.subject-admin', 'Unmatch Detected');
        $this->createLocalizationKey('unmatch.title-admin', 'Unmatch Detected');
        $this->createLocalizationKey('unmatch.body-admin', '{firstName} and {secondName} have unmatched.');

        $this->createLocalizationKey('unmatch.subject-user', 'Someone Has Unmatched With One Of Your Projects');
        $this->createLocalizationKey('unmatch.title-user', 'Someone Has Unmatched With One Of Your Projects');
        $this->createLocalizationKey('unmatch.body-user', '{firstName} has unmatched with one of your projects.');

        //upcoming-progress-update
        $this->createLocalizationKey('upcoming-progress-update.subject', 'Report Due');
        $this->createLocalizationKey('upcoming-progress-update.title', 'Report Due');
        $this->createLocalizationKey('upcoming-progress-update.body', ' You are due to submit a progress update report for {pitchName} in 30 days.<br><br>' .
            'Click below to to create your report.');
        $this->createLocalizationKey('upcoming-progress-update.cta', 'Create Report');

        //update-visibility
        $this->createLocalizationKey('update-visibility.subject', 'Update Your Project\'s Funding Status');
        $this->createLocalizationKey('update-visibility.title', 'Update Your Project\'s Funding Status');
        $this->createLocalizationKey('update-visibility.body', 'It\'s been three days since someone matched with one of your projects. ' .
            'Do you need to update its funding status?');
        $this->createLocalizationKey('update-visibility.cta', 'Update Funding Status');

        //user-invited
        $this->createLocalizationKey('user-invited.subject', 'Create Your Account');
        $this->createLocalizationKey('user-invited.title', 'Create Your Account');
        $this->createLocalizationKey('user-invited.body-admin', 'You\'ve been invited to the administration.<br><br>' .
            'Follow this link to create your account.');
        $this->createLocalizationKey('user-invited.body-user', 'You\'ve been invited to an organisation.<br><br>' .
            'Follow this link to create your account.');
        $this->createLocalizationKey('user-invited.cta', 'Create Account');

        //user-verification
        $this->createLocalizationKey('user-verification.subject', 'Verify Your Email Address');
        $this->createLocalizationKey('user-verification.title', 'VERIFY YOUR EMAIL ADDRESS');
        $this->createLocalizationKey('user-verification.body', 'Follow the below link to verify your email address. It\'s valid for 48 hours.  If the link does not work, log on ' .
            'to TerraMatch and resubmit a verfication request. <br>' .
            'If you continue to have problems accessing your account, feel free to message us at info@terramatch.org.' .
            '<br><br>-----<br><br>' .
            'Suivez le lien ci-dessous pour vérifier votre adresse e-mail. Ce lien est valable pendant 48 heures.  Si le lien ne fonctionne pas, ' .
            'connectez-vous à TerraMatch et soumettez à nouveau une demande de vérification.<br>' .
            'Si vous continuez à avoir des problèmes pour accéder à votre compte, n\'hésitez pas à nous envoyer un message à l\'adresse info@terramatch.org.');
        $this->createLocalizationKey('user-verification.cta', 'VERIFY EMAIL ADDRESS');

        //v2-project-invite-received
        $this->createLocalizationKey('v2-project-invite-received.subject', 'You have been invited to join TerraMatch');
        $this->createLocalizationKey('v2-project-invite-received.title', 'You have been invited to join TerraMatch');
        $this->createLocalizationKey('v2-project-invite-received.body', '{organisationName} has invited you to join TerraMatch as a monitoring
            partner to {name}. Set an account password today to see the project’s
            progress and access their latest reports.<br><br>
            Reset your password <a href="{callbackUrl}" style="color: #6E6E6E;">Here.</a><br><br>');

        //v2-project-monitoring-notification
        $this->createLocalizationKey('v2-project-monitoring-notification.subject', 'You have been added as a monitoring partner.');
        $this->createLocalizationKey('v2-project-monitoring-notification.title', 'You have been added as a monitoring partner.');
        $this->createLocalizationKey('v2-project-monitoring-notification.body', 'You have been added to {name} as a monitoring partner on TerraMatch. Login into your account
            today to see the project progress and relevant reports.<br><br>
            Login <a href="{callbackUrl}" style="color: #6E6E6E;">Here.</a><br><br>');

        // version-approved
        $this->createLocalizationKey('version-approved.subject', 'Your Changes Have Been Approved');
        $this->createLocalizationKey('version-approved.title', 'Your Changes Have Been Approved');
        $this->createLocalizationKey('version-approved.body', 'Your changes to {versionName} have been approved.<br><br>' .
            'Follow this link to view the changes.');
        $this->createLocalizationKey('version-approved.cta', 'View Changes');

        // version-created
        $this->createLocalizationKey('version-created.subject', 'CHANGES REQUIRING YOUR APPROVAL ');
        $this->createLocalizationKey('version-created.title', 'Changes Requiring Your Approval');
        $this->createLocalizationKey('version-created.body', 'Changes have been made to {versionName}. Follow this link to review the changes.');
        $this->createLocalizationKey('version-created.cta', 'Review Changes');

        // version-rejected
        $this->createLocalizationKey('version-rejected.subject', 'Your Changes Have Been Rejected');
        $this->createLocalizationKey('version-rejected.title', 'Your Changes Have Been Rejected');
        $this->createLocalizationKey('version-rejected.body', 'Your changes to {versionName} have been rejected. {explanation}. Follow this link to view the changes.<br><br>' .
            'If you have any questions, feel free to message us at info@terramatch.org.');
        $this->createLocalizationKey('version-rejected.cta', 'View Changes');

        // report-reminder
        $this->createLocalizationKey('report-reminder.subject', 'Reminder: Your {entityTypeName} Still Needs Your Input');
        $this->createLocalizationKey('report-reminder.title', 'Reminder: Your {entityTypeName} Still Needs Your Input');
        $this->createLocalizationKey('report-reminder.body', 'This is a reminder that your {entityTypeName} for {entityModelName} still has the status: {entityStatus}. Below you will see a note from your project manager about the report.<br><br> 
            Here is a link to the reporting task on TerraMatch so you can easily access your report: <a href="{callbackUrl}" style="color: #6E6E6E;">Here.</a> If you have any questions, please reach out to your project manager or to info@terramatch.org.<br><br>{feedback}');

        // task-digest
        $this->createLocalizationKey('task-digest.subject', '{projectName} - Report Summary for {date}');
        $this->createLocalizationKey('task-digest.title', 'Action Items Summary - Task Due {date}');
        $this->createLocalizationKey('task-digest.body', 'Please note: this digest summarizes any reports that require engagement or were approved in the past week. Any reports already approved will not be mentioned below, since they do not require any action. Once all reports in this task are approved, the task status will be changed to approved, and you’ll no longer receive this digest.
            <table class="full-width-fixed-table">
            <tr class="border-light-gray">
                <th class="border-light-gray" style="width: 25%;">Submission State</th>
                <th class="border-light-gray" style="width: 25%;">Report Name</th>
                <th class="border-light-gray" style="width: 25%;">Status</th>
                <th class="border-light-gray" style="width: 25%;">Change Request</th>
                <th class="border-light-gray" style="width: 25%;">Latest comments</th>
            </tr>
            {reportData}
            </table>');

        $this->createLocalizationKey('task-digest.cta', 'Access this task here');

        // task-digest project-manager
        $this->createLocalizationKey('task-digest-project-manager.subject', 'PLEASE REVIEW REPORT UPDATE');
        $this->createLocalizationKey('task-digest-project-manager.title', 'PLEASE REVIEW REPORT UPDATE');
        $this->createLocalizationKey('task-digest-project-manager.body', 'The project {projectName} has submitted an update to their report {reportName}, due {reportDueAt}. Please review the update and either approve it or request for more information. 
            You are receiving this message because you are associated with this project as a Project Manager in TerraMatch. If you wish to no longer receive these messages or have any issues seeing or responding to the changes, please reach out to info@terramatch.org.');
        $this->createLocalizationKey('task-digest-project-manager.cta', 'View Report');

        // //project-manager-project
        $this->createLocalizationKey('project-manager-project.subject', 'Please Review Project Profile Update');
        $this->createLocalizationKey('project-manager-project.title', 'Please Review Project Profile Update');
        $this->createLocalizationKey('project-manager-project.body', 'The {projectName}. has submitted an update to their project that needs to be reviewed. '.
                'Please review the project and either accept the submission or request for more information.<br><br>'.
                'You are receiving this message because you are associated with this project as a Project Manager in TerraMatch.  '.
                'If you wish to no longer recieve these messages or have any issues seeing or responding to the changes, please reach out to info@terramatch.org');
        $this->createLocalizationKey('project-manager-project.cta', 'View {entityTypeName}');

        //project-manager-site
        $this->createLocalizationKey('project-manager-site.subject', 'A Site Has Been Submitted for Your Review');
        $this->createLocalizationKey('project-manager-site.title', 'A Site Has Been Submitted for Your Review');
        $this->createLocalizationKey('project-manager-site.body', 'The project {projectName} has submitted the site {entityName} for your review. '.
                'Please review the site and either accept the submission or request for more information.<br><br>'.
                'You are receiving this message because you are associated with this project as a Project Manager in TerraMatch.  '.
                'If you wish to no longer recieve these messages or have any issues seeing or responding to the changes, please reach out to info@terramatch.org');
        $this->createLocalizationKey('project-manager-site.cta', 'View {entityTypeName}');

        //project-manager-nursery
        $this->createLocalizationKey('project-manager-nursery.subject', 'A Nursery Has Been Submitted for Your Review');
        $this->createLocalizationKey('project-manager-nursery.title', 'A Nursery Has Been Submitted for Your Review');
        $this->createLocalizationKey('project-manager-nursery.body', 'The project {projectName} has submitted the nursery {entityName} for your review. '.
                'Please review the nursery and either accept the submission or request for more information.<br><br>'.
                'You are receiving this message because you are associated with this project as a Project Manager in TerraMatch.  If you wish to no longer recieve these messages or have any issues seeing or responding to the changes, please reach out to info@terramatch.org');
        $this->createLocalizationKey('project-manager-nursery.cta', 'View {entityTypeName}');

        $this->createLocalizationKey('v2-project-invite-received-create.subject', 'YOU HAVE BEEN INVITED TO JOIN TERRAMATCH');
        $this->createLocalizationKey('v2-project-invite-received-create.title', 'YOU HAVE BEEN INVITED TO JOIN TERRAMATCH');
        $this->createLocalizationKey('v2-project-invite-received-create.body', '{organisationName} has invited you to join TerraMatch as a monitoring partner on {projectName}
            Click the link below to create your account and set your password so you can see the project’s progress and access its reports.');
        $this->createLocalizationKey('v2-project-invite-received-create.cta', 'CREATE ACCOUNT');

        // terrafund-polygon-update
        $this->createLocalizationKey('terrafund-polygon-update.subject', 'Terrafund Polygon Update');
        $this->createLocalizationKey('terrafund-polygon-update.dqatopd.title', 'Monitoring Partners to DQA Updates');
        $this->createLocalizationKey('terrafund-polygon-update.pdtodqa.title', 'DQA to Monitoring Partners Updates');
        $this->createLocalizationKey('terrafund-polygon-update.cta', 'View Updates');
        $this->createLocalizationKey(
            'terrafund-polygon-update.body',
            '<table style="margin: 0 32px;">'.
            '<tr>'.
                '<td>'.
                    '<p style="font-size: 14px; color: #353535; font-family: \'Inter\', sans-serif;">Dear {userName},</p>'.
                    '<p style="font-size: 14px; color: #353535; font-family: \'Inter\', sans-serif;">Please find below the weekly update on polygon versions, statuses, and comments.</p><br>'.
                    '<p style="text-align: start; margin: 0;"><strong style="font-size: 14px; color: #353535; font-family: \'Inter\', sans-serif;">Polygon Version Update</strong></p><br>'.
                    '<table>'.
                        '<tr>'.
                            '<td style="overflow: hidden; border: 1px solid #ddd; border-radius:10px; display: {hasUpdateChange};">'.
                                '<table style="width: 100%; border-collapse: collapse;">'.
                                    '<thead>'.
                                        '<tr>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; border-left:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Project Name</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Site Name</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Polygon Name</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Version ID</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Change</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Updated by</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600; border-right:hidden;">Comment</th>'.
                                        '</tr>'.
                                    '</thead>'.
                                    '<tbody>'.
                                        '{polygonUpdateTable}'.
                                    '</tbody>'.
                                '</table>'.
                            '</td>'.
                        '</tr>'.
                    '</table>'.
                    '<br><br>'.
                    '<p style="text-align: start; margin: 0;"><strong style="font-size: 14px; color: #353535; font-family: \'Inter\', sans-serif;">Polygon Polygon Status Update</strong></p><br>'.
                    '<table>'.
                        '<tr>'.
                            '<td style="overflow: hidden; border: 1px solid #ddd; border-radius:10px; display: {hasStatusChange};">'.
                                '<table style="width: 100%; border-collapse: collapse;">'.
                                    '<thead>'.
                                        '<tr>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; border-left:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Project Name</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Site Name</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Polygon Name</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Version ID</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Change</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Updated by</th>'.
                                            '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600; border-right:hidden;">Comment</th>'.
                                        '</tr>'.
                                    '</thead>'.
                                    '<tbody>'.
                                        '{polygonStatusTable}'.
                                    '</tbody>'.
                                '</table>'.
                            '</td>'.
                        '</tr>'.
                    '</table>'.
                    '<br><br>'.
                    '<p style="text-align: start; font-family: \'Inter\', sans-serif; font-size: 14px; color: #353535;">Best regards,</p>'.
                    '<p style="text-align: start; margin: 0; font-family: \'Inter\', sans-serif; font-size: 14px; color: #353535;"><strong>TerraMatch Support</strong></p><br>'.
                '</td>'.
            '</tr>'.
        '</table>'
        );
    }

    public function createLocalizationKey($key, $value): void
    {
        if (LocalizationKey::where('key', operator: $key)->exists()) {
            return;
        }

        $localizationKey = LocalizationKey::create([
            'key' => $key,
            'value' => $value,
        ]);

        $localizationKey->value_id = I18nHelper::generateI18nItem($localizationKey, 'value');
        $localizationKey->save();
    }
}
