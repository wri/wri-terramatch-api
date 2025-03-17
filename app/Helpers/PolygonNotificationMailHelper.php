<?php

namespace App\Helpers;

class PolygonNotificationMailHelper
{
    public static function getPolygonNotificationMail(bool $isManager)
    {
        return (
            '<table style="margin: 0 32px;">'.
                '<tr>'.
                    '<td>'.
                        '<p style="text-align: start; font-size: 14px; color: #353535; font-family: \'Inter\', sans-serif;">Dear {userName},</p>'.
                        '<p style="text-align: start; font-size: 14px; color: #353535; font-family: \'Inter\', sans-serif;">'.
                            'Please find below your weekly update on polygon versions, statuses, and comments within the {projectName} project on TerraMatch.'.
                        '</p>'.
                        '<br>'.
                        '<p style="text-align: start; margin: 0; display: {hasUpdateChange};">'.
                            '<strong style="font-size: 14px; color: #353535; font-family: \'Inter\', sans-serif;">'.
                                'Polygon Version Update'.
                            '</strong>'.
                        '</p>'.
                        '<br style="display: {hasUpdateChange};">'.
                        '<table style="table-layout: fixed; display: {hasUpdateChange};">'.
                            '<tr>'.
                                '<td style="overflow: hidden; border: 1px solid #ddd; border-radius:10px;">'.
                                    '<table style="width: 100%; border-collapse: collapse;">'.
                                        '<thead>'.
                                            '<tr>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600; border-left:hidden;">Project Name</th>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Site Name</th>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Polygon Name</th>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Version ID</th>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Change</th>'.
                                                ($isManager ? '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Updated by</th>' : '').
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
                        '<br style="display: {hasUpdateChange};"><br style="display: {hasUpdateChange};">'.
                        '<p style="text-align: start; margin: 0; display: {hasStatusChange};">'.
                            '<strong style="font-size: 14px; color: #353535; font-family: \'Inter\', sans-serif;">'.
                                'Polygon Status Update'.
                            '</strong>'.
                        '</p>'.
                        '<br style="display: {hasStatusChange};">'.
                        '<table style="table-layout: fixed; display: {hasStatusChange};">'.
                            '<tr>'.
                                '<td style="overflow: hidden; border: 1px solid #ddd; border-radius:10px;">'.
                                    '<table style="width: 100%; border-collapse: collapse;">'.
                                        '<thead>'.
                                            '<tr>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600; border-left:hidden;">Project Name</th>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Site Name</th>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Polygon Name</th>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Old Status</th>'.
                                                '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">New Status</th>'.
                                                ($isManager ? '<th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #F7F7F7; border-top:hidden; font-size: 14px; color: #00263399; font-family: \'Inter\', sans-serif; font-weight: 600;">Updated by</th>' : '').
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
                        '<br style="display: {hasStatusChange};"><br style="display: {hasStatusChange};">'.
                        '<p style="text-align: start; font-family: \'Inter\', sans-serif; font-size: 14px; color: #353535;">'.
                            'Best regards,'.
                        '</p>'.
                        '<p style="text-align: start; margin: 0; font-family: \'Inter\', sans-serif; font-size: 14px; color: #353535;">'.
                            '<strong>TerraMatch Support</strong>'.
                        '</p>'.
                    '</td>'.
                '</tr>'.
            '</table>'
        );
    }
}
