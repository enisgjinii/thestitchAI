<?php
/** @var array<string, string> $brand */
$brand = TheStitch_Payment_Email_Brand::luxury_tokens();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Request</title>
</head>
<body style="margin:0;padding:24px;background:<?php echo esc_attr($brand['outer_bg']); ?>;font-family:<?php echo esc_attr($brand['font_stack']); ?>;color:<?php echo esc_attr($brand['heading_text']); ?>;">
    <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid <?php echo esc_attr($brand['card_border']); ?>;border-radius:18px;overflow:hidden;box-shadow:0 14px 36px rgba(32,22,8,.08);">
        <div style="padding:26px 28px;background:<?php echo esc_attr($brand['hero_bg']); ?>;color:<?php echo esc_attr($brand['hero_text']); ?>;">
            <?php if ($logo_url) : ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" style="max-height:44px;max-width:180px;margin-bottom:14px;display:block;margin-left:auto;margin-right:auto;">
            <?php else : ?>
                <div style="font-size:13px;letter-spacing:.08em;text-transform:uppercase;opacity:.92;text-align:center;">The Stitch</div>
            <?php endif; ?>
            <h1 style="margin:8px 0 0;font-size:28px;line-height:1.2;text-align:center;font-weight:700;">Your Finalized Quote</h1>
            <p style="margin:10px 0 0;font-size:15px;line-height:1.5;opacity:.96;text-align:center;">Hello <?php echo $customer_name; ?>, your custom <?php echo strtolower($order_type); ?> quote is ready.</p>
        </div>

        <div style="padding:24px 28px;">
            <?php if (!empty($test_mode)) : ?>
            <div style="margin-bottom:18px;background:<?php echo esc_attr($brand['test_bg']); ?>;border:1px solid <?php echo esc_attr($brand['test_border']); ?>;border-radius:12px;padding:14px 16px;color:<?php echo esc_attr($brand['test_text']); ?>;font-size:13px;line-height:1.6;">
                <strong>Test email.</strong> The Pay Securely button uses a placeholder link only. Replace it with the real NOMOD payment URL before sending to customers.
            </div>
            <?php endif; ?>

            <div style="padding:18px 20px;background:<?php echo esc_attr($brand['section_bg']); ?>;border:1px solid <?php echo esc_attr($brand['section_border']); ?>;border-radius:12px;">
                            <div style="font-size:13px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px;">Quote Summary</div>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                    <tr>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['label_text']); ?>;font-size:14px;width:42%;">Reference</td>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['heading_text']); ?>;font-size:14px;font-weight:700;text-align:right;"><?php echo $reference; ?></td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['label_text']); ?>;font-size:14px;">Request Type</td>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['heading_text']); ?>;font-size:14px;font-weight:600;text-align:right;"><?php echo $order_type; ?></td>
                    </tr>
                    <?php if ($referral_code !== '') : ?>
                    <tr>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['label_text']); ?>;font-size:14px;">Referral Code</td>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['heading_text']); ?>;font-size:14px;font-weight:600;text-align:right;"><?php echo esc_html($referral_code); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding:10px 0;color:<?php echo esc_attr($brand['label_text']); ?>;font-size:14px;">Final Price</td>
                        <td style="padding:10px 0;color:<?php echo esc_attr($brand['heading_text']); ?>;font-size:24px;font-weight:700;text-align:right;"><?php echo esc_html($currency . ' ' . $final_price); ?></td>
                    </tr>
                </table>
            </div>

            <?php if ($admin_message) : ?>
            <div style="margin-top:18px;padding:16px 18px;background:<?php echo esc_attr($brand['note_bg']); ?>;border:1px solid <?php echo esc_attr($brand['note_border']); ?>;border-left:4px solid #111111;border-radius:12px;color:<?php echo esc_attr($brand['body_text']); ?>;font-size:14px;line-height:1.7;">
                <?php echo $admin_message; ?>
            </div>
            <?php endif; ?>

            <?php if ($design_preview_url) : ?>
            <div style="margin-top:22px;text-align:center;">
                <div style="font-size:13px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px;">Design Preview</div>
                <img src="<?php echo esc_url($design_preview_url); ?>" alt="Design preview" style="max-width:220px;max-height:220px;width:220px;height:auto;border-radius:12px;border:1px solid <?php echo esc_attr($brand['section_border']); ?>;display:inline-block;">
            </div>
            <?php endif; ?>

            <?php if ($fabric_url) : ?>
            <div style="margin-top:22px;text-align:center;">
                <div style="font-size:13px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px;">Fabric / Pattern Reference</div>
                <img src="<?php echo esc_url($fabric_url); ?>" alt="Fabric or pattern reference" style="max-width:220px;max-height:220px;width:220px;height:auto;border-radius:12px;border:1px solid <?php echo esc_attr($brand['section_border']); ?>;display:inline-block;">
            </div>
            <?php endif; ?>

            <?php if (!empty($upload_groups)) : ?>
            <div style="margin-top:22px;">
                <div style="font-size:13px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px;">Uploaded Inspiration</div>
                <?php foreach ($upload_groups as $group_label => $images) : ?>
                    <p style="margin:0 0 8px;color:<?php echo esc_attr($brand['label_text']); ?>;font-size:13px;font-weight:700;"><?php echo esc_html($group_label); ?></p>
                    <?php foreach ($images as $image) : ?>
                        <div style="margin-bottom:12px;text-align:center;">
                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['label']); ?>" style="max-width:180px;max-height:180px;width:180px;height:auto;object-fit:cover;border-radius:10px;border:1px solid <?php echo esc_attr($brand['section_border']); ?>;display:inline-block;margin:0 8px 8px 0;">
                            <div style="font-size:12px;color:<?php echo esc_attr($brand['label_text']); ?>;margin-top:4px;"><?php echo esc_html($image['label']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($preview_3d_url) : ?>
            <div style="margin-top:18px;text-align:center;">
                <a href="<?php echo esc_url($preview_3d_url); ?>" style="display:inline-block;color:<?php echo esc_attr($brand['heading_text']); ?>;font-size:14px;font-weight:600;text-decoration:underline;">View your 3D design</a>
            </div>
            <?php endif; ?>

            <?php if (!empty($garment_config)) : ?>
            <div style="margin-top:22px;padding:16px 18px;background:<?php echo esc_attr($brand['note_bg']); ?>;border:1px solid <?php echo esc_attr($brand['note_border']); ?>;border-radius:12px;">
                <div style="font-size:13px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;margin-bottom:8px;">Garment Configuration</div>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                    <?php foreach ($garment_config as $label => $value) : ?>
                    <tr>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['label_text']); ?>;font-size:13px;width:42%;"><?php echo esc_html($label); ?></td>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['heading_text']); ?>;font-size:13px;"><?php echo esc_html($value); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>

            <?php if (!empty($sizing_details)) : ?>
            <div style="margin-top:18px;padding:16px 18px;background:<?php echo esc_attr($brand['note_bg']); ?>;border:1px solid <?php echo esc_attr($brand['note_border']); ?>;border-radius:12px;">
                <div style="font-size:13px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;margin-bottom:8px;">Sizing Details</div>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                    <?php foreach ($sizing_details as $label => $value) : ?>
                    <tr>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['label_text']); ?>;font-size:13px;width:42%;"><?php echo esc_html($label); ?></td>
                        <td style="padding:8px 0;border-bottom:1px solid <?php echo esc_attr($brand['row_border']); ?>;color:<?php echo esc_attr($brand['heading_text']); ?>;font-size:13px;"><?php echo esc_html($value); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>

            <div style="margin-top:26px;text-align:center;">
                <a href="<?php echo esc_url($payment_url); ?>" style="display:inline-block;background:<?php echo esc_attr($brand['cta_bg']); ?>;color:<?php echo esc_attr($brand['cta_text']); ?>;text-decoration:none;padding:14px 28px;border-radius:999px;font-size:16px;font-weight:700;">Pay Securely</a>
                <p style="margin:16px 0 0;color:<?php echo esc_attr($brand['label_text']); ?>;font-size:13px;line-height:1.6;">You will be redirected to our secure NOMOD checkout to complete payment.</p>
                <p style="margin:10px 0 0;color:#9a8d79;font-size:12px;line-height:1.6;">Available payment options may include cards, Apple Pay, Google Pay, Tabby and Tamara, depending on availability in the secure NOMOD checkout.</p>
            </div>
        </div>

        <div style="padding:16px 28px;border-top:1px solid #f2e8da;background:<?php echo esc_attr($brand['footer_bg']); ?>;color:<?php echo esc_attr($brand['footer_text']); ?>;font-size:12px;line-height:1.7;text-align:center;">
            Questions about your quote? Reply to this email<?php echo $support_email ? ' or contact us at <a href="mailto:' . esc_attr($support_email) . '" style="color:#111111;">' . esc_html($support_email) . '</a>' : ''; ?>.<br>
            © <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($site_name); ?>
        </div>
    </div>
</body>
</html>
