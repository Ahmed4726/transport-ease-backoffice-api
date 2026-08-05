<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Received</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="padding: 20px 0; background-color: #f4f4f4;">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="padding: 32px; text-align: center; background-color: #007bff; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 28px;">Welcome to Transport Ease</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <p style="font-size: 16px; line-height: 1.6; margin: 0 0 20px;">Hello {{ $name }},</p>
                            <p style="font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                Thank you for signing up as a {{ $role }} with Transport Ease. We have received your registration request and are reviewing your details now.
                            </p>
                            <p style="font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                Our team will respond within the next 24-48 hours with the status of your application. In the meantime, please keep an eye on your inbox for any additional information we may request.
                            </p>
                            <p style="font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                If you submitted driver documents, we will also verify them and notify you once your account is approved.
                            </p>
                            <p style="font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                Thanks again for choosing Transport Ease.
                            </p>
                            <p style="font-size: 16px; line-height: 1.6; margin: 0; font-weight: bold;">Best regards,<br>Transport Ease Team</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 32px 32px; background-color: #f4f4f4; font-size: 12px; color: #777; text-align: center;">
                            <p style="margin: 0;">This email confirms receipt of your registration request. Please do not reply to this address.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
