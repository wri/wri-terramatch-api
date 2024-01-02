<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
    </head>
    <body bgcolor="#F7F7F7">
        <table cellpadding="0" cellspacing="0" border="0" bgcolor="#F7F7F7" width="100%">
            <tr>
                <td style="padding-left: 16px; padding-right: 16px;">
                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF" width="500" align="center">
                        <tr bgcolor="#F7F7F7">
                            <td height="16"></td>
                        </tr>
                        <tr>
                            <td height="32"></td>
                        </tr>
                        <tr>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0" width="500">
                                    <tr>
                                        <td width="32"></td>
                                        <td width="436" style="font-family: sans-serif; font-size: 22px; color: #000000;">
                                            <img src="{!! $backend_url !!}/images/email_logo.gif" width="118" height="32" style="display: block; border: 0;" alt="TerraMatch">
                                        </td>
                                        <td width="32"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td height="32"></td>
                        </tr>
                        <tr bgcolor="#27A9E0">
                            <td height="4"></td>
                        </tr>
                        @if (!empty($banner))
                            <tr>
                                <td>
                                    <img src="{!! $backend_url !!}/images/email_banner_{!! $banner !!}_1.jpg" width="500" height="170" style="display: block; border: 0;">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="0" border="0" width="500">
                                        <tr>
                                            <td width="193">
                                                <img src="{!! $backend_url !!}/images/email_banner_{!! $banner !!}_2.jpg" width="193" height="114" style="display: block; border: 0;">
                                            </td>
                                            <td width="114">
                                                <img src="{!! $backend_url !!}/images/email_banner_{!! $banner !!}_3c.gif" width="114" height="114" style="display: block; border: 0;">
                                            </td>
                                            <td width="193">
                                                <img src="{!! $backend_url !!}/images/email_banner_{!! $banner !!}_4.jpg" width="193" height="114" style="display: block; border: 0;">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td height="32"></td>
                        </tr>
                        <tr>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0" width="500">
                                    <tr>
                                        <td width="64"></td>
                                        <td width="372" style="font-family: sans-serif; font-size: 22px; color: #000000; text-align: center; text-transform: uppercase;">
                                            <strong>{!! $title !!}</strong>
                                        </td>
                                        <td width="64"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td height="32"></td>
                        </tr>
                        <tr>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0" width="500">
                                    <tr>
                                        <td width="32"></td>
                                        <td width="436" style="font-family: serif; font-size: 14px; color: #000000; text-align: center;">
                                            {!! $body !!}
                                        </td>
                                        <td width="32"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td height="32"></td>
                        </tr>
                        @if (!empty($link) && !empty($cta))
                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="0" border="0" width="500">
                                        <tr>
                                            <td width="96" height="32"></td>
                                            <td width="308" height="32" bgcolor="#27A9E0" style="font-family: sans-serif; font-size: 14px; color: #000000; text-align: center;border-radius: 6px;">
                                                <a href="{!! substr(strtolower($link), 0, 4) === 'http' ? '' :  $frontend_url !!}{!! $link !!}" style="color: #000000; text-transform: uppercase; text-decoration: none;border-radius: 6px;">
                                                    <strong>{!! $cta !!}</strong>
                                                </a>
                                            </td>
                                            <td width="96" height="32"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="32"></td>
                            </tr>
                        @endif
                        <tr bgcolor="#F7F7F7">
                            <td height="16"></td>
                        </tr>
                        <tr bgcolor="#F7F7F7">
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0" width="500">
                                    <tr>
                                        <td width="32"></td>
                                        <td width="436" style="font-family: serif; font-size: 14px; color: #6E6E6E; text-align: center;">
                                            @if ($monitoring)
                                                You are receiving this email because you have an account with TerraMatch.
                                                This is a required email and is not a marketing or promotional email.
                                                You are therefore unable to unsubscribe.
                                                <br><br>
                                            @elseif ($invite)
                                                You are receiving this email because a TerraMatch user has
                                                invited you to join the platform. This is a required email
                                                and is not a marketing or promotional email. You are therefore unable to unsubscribe.
                                                <br><br>
                                            @else
                                                @if ($transactional)
                                                    You are receiving this email because you have an account with TerraMatch.
                                                    This is a required email and is not a marketing or promotional email.
                                                    You are therefore unable to unsubscribe.
                                                    <br><br>
                                                @else
                                                    You are receiving this email because you have an account with TerraMatch.
                                                    This is not a required email.
                                                    <a href="{!! $backend_url !!}{!! $unsubscribe !!}" style="color: #6E6E6E;">Click here</a> to unsubscribe.
                                                    <br><br>
                                                @endif
                                            @endif
                                            If you have any questions, feel free to message us at <a href="mailto:TerraMatch@wri.org" style="color: #6E6E6E;">TerraMatch@wri.org</a>.
                                            <br><br>
                                            &copy; WRI {!! $year !!}
                                        </td>
                                        <td width="32"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr bgcolor="#F7F7F7">
                            <td height="16"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
