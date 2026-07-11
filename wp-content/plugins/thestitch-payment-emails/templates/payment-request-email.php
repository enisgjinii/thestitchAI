<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Request</title>
</head>
<body style="margin:0;padding:0;background-color:#f7f2ea;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f2ea;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(47,42,34,0.08);">
                <tr>
                    <td style="background:linear-gradient(135deg,#2f2a22 0%,#5c4f3d 100%);padding:32px 28px;text-align:center;">
                        <?php if ($logo_url) : ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" style="max-height:48px;max-width:180px;margin-bottom:16px;">
                        <?php else : ?>
                            <div style="font-size:24px;font-weight:700;color:#f7f2ea;letter-spacing:0.04em;margin-bottom:8px;"><?php echo esc_html($site_name); ?></div>
                        <?php endif; ?>
                        <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:700;">Your Finalized Quote</h1>
                        <p style="margin:10px 0 0;color:rgba(255,255,255,0.92);font-size:15px;">Hello <?php echo $customer_name; ?>, your custom design quote is ready.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf7f1;border:1px solid #ece3d4;border-radius:12px;">
                            <tr>
                                <td style="padding:20px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="color:#7a6a4e;font-size:14px;padding:6px 0;">Reference</td>
                                            <td style="color:#2f2a22;font-size:14px;font-weight:700;text-align:right;padding:6px 0;"><?php echo $reference; ?></td>
                                        </tr>
                                        <tr>
                                            <td style="color:#7a6a4e;font-size:14px;padding:6px 0;">Order Type</td>
                                            <td style="color:#2f2a22;font-size:14px;font-weight:600;text-align:right;padding:6px 0;"><?php echo $order_type; ?></td>
                                        </tr>
                                        <?php if ($referral_code !== '') : ?>
                                        <tr>
                                            <td style="color:#7a6a4e;font-size:14px;padding:6px 0;">Referral Code</td>
                                            <td style="color:#2f2a22;font-size:14px;font-weight:600;text-align:right;padding:6px 0;"><?php echo esc_html($referral_code); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td style="color:#7a6a4e;font-size:14px;padding:6px 0;">Final Price</td>
                                            <td style="color:#2f2a22;font-size:22px;font-weight:700;text-align:right;padding:6px 0;"><?php echo esc_html($currency . ' ' . $final_price); ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php if ($admin_message) : ?>
                <tr>
                    <td style="padding:0 28px 20px;">
                        <div style="background:#fff8eb;border-left:4px solid #d4a853;border-radius:8px;padding:16px 18px;color:#4a3f31;font-size:14px;line-height:1.7;">
                            <?php echo $admin_message; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <?php if ($design_preview_url) : ?>
                <tr>
                    <td style="padding:0 28px 20px;text-align:center;">
                        <h2 style="margin:0 0 12px;color:#2f2a22;font-size:18px;">Design Preview</h2>
                        <img src="<?php echo esc_url($design_preview_url); ?>" alt="Design preview" style="max-width:100%;height:auto;border-radius:12px;border:1px solid #ece3d4;">
                    </td>
                </tr>
                <?php endif; ?>

                <?php if ($fabric_url) : ?>
                <tr>
                    <td style="padding:0 28px 20px;text-align:center;">
                        <h2 style="margin:0 0 12px;color:#2f2a22;font-size:18px;">Fabric / Pattern Reference</h2>
                        <img src="<?php echo esc_url($fabric_url); ?>" alt="Fabric or pattern reference" style="max-width:100%;height:auto;border-radius:12px;border:1px solid #ece3d4;">
                    </td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($upload_groups)) : ?>
                <tr>
                    <td style="padding:0 28px 20px;">
                        <h2 style="margin:0 0 12px;color:#2f2a22;font-size:18px;">Uploaded Inspiration</h2>
                        <?php foreach ($upload_groups as $group_label => $images) : ?>
                            <p style="margin:0 0 8px;color:#7a6a4e;font-size:13px;font-weight:700;"><?php echo esc_html($group_label); ?></p>
                            <?php foreach ($images as $image) : ?>
                                <div style="margin-bottom:12px;text-align:center;">
                                    <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['label']); ?>" style="max-width:100%;height:auto;border-radius:10px;border:1px solid #ece3d4;">
                                    <div style="font-size:12px;color:#7a6a4e;margin-top:4px;"><?php echo esc_html($image['label']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>

                <?php if ($preview_3d_url) : ?>
                <tr>
                    <td style="padding:0 28px 20px;text-align:center;">
                        <a href="<?php echo esc_url($preview_3d_url); ?>" style="display:inline-block;color:#2f2a22;font-size:14px;font-weight:600;text-decoration:underline;">View your 3D design</a>
                    </td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($garment_config)) : ?>
                <tr>
                    <td style="padding:0 28px 20px;">
                        <h2 style="margin:0 0 12px;color:#2f2a22;font-size:18px;">Garment Configuration</h2>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <?php foreach ($garment_config as $label => $value) : ?>
                            <tr>
                                <td style="padding:8px 0;border-bottom:1px solid #f1ebe1;color:#7a6a4e;font-size:13px;width:42%;"><?php echo esc_html($label); ?></td>
                                <td style="padding:8px 0;border-bottom:1px solid #f1ebe1;color:#2f2a22;font-size:13px;"><?php echo esc_html($value); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($sizing_details)) : ?>
                <tr>
                    <td style="padding:0 28px 24px;">
                        <h2 style="margin:0 0 12px;color:#2f2a22;font-size:18px;">Sizing Details</h2>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <?php foreach ($sizing_details as $label => $value) : ?>
                            <tr>
                                <td style="padding:8px 0;border-bottom:1px solid #f1ebe1;color:#7a6a4e;font-size:13px;width:42%;"><?php echo esc_html($label); ?></td>
                                <td style="padding:8px 0;border-bottom:1px solid #f1ebe1;color:#2f2a22;font-size:13px;"><?php echo esc_html($value); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>

                <tr>
                    <td style="padding:0 28px 28px;text-align:center;">
                        <a href="<?php echo esc_url($payment_url); ?>" style="display:inline-block;background:#2f2a22;color:#ffffff;text-decoration:none;padding:16px 32px;border-radius:999px;font-size:16px;font-weight:700;">Pay Securely</a>
                        <p style="margin:16px 0 0;color:#7a6a4e;font-size:13px;line-height:1.6;">You will be redirected to our secure NOMOD checkout to complete payment.</p>
                        <p style="margin:10px 0 0;color:#9a8d79;font-size:12px;line-height:1.6;">Available payment options may include cards, Apple Pay, Google Pay, Tabby and Tamara, depending on availability in the secure NOMOD checkout.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:24px 28px;background:#faf7f1;border-top:1px solid #ece3d4;text-align:center;">
                        <p style="margin:0 0 8px;color:#7a6a4e;font-size:13px;">Questions about your quote? Reply to this email or contact us<?php echo $support_email ? ' at <a href="mailto:' . esc_attr($support_email) . '" style="color:#2f2a22;">' . esc_html($support_email) . '</a>' : ''; ?>.</p>
                        <p style="margin:0;color:#b0a491;font-size:12px;">&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($site_name); ?>. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
