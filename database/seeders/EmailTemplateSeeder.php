<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Enums\TemplateType;
use Illuminate\Support\Str;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->getAllTemplates();

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        $this->command->info('✅ Created ' . count($templates) . ' email templates!');
    }

    private function getAllTemplates(): array
    {
        return array_merge(
            $this->getAuthTemplates(),
            $this->getWelcomeTemplates(),
            $this->getMarketingTemplates(),
            $this->getTransactionalTemplates(),
            $this->getNewsletterTemplates(),
            $this->getEventTemplates(),
            $this->getEcommerceTemplates(),
        );
    }

    private function getAuthTemplates(): array
    {
        return [
            $this->createTemplate(
                'Forgot Password',
                'Reset Your Password - {app_name}',
                TemplateType::AUTHENTICATION,
                'Password reset email with security tips',
                $this->getPasswordReset(),
                true,
                false
            ),
        ];
    }

    private function getWelcomeTemplates(): array
    {
        return [
            $this->createTemplate(
                'Modern Welcome - Blue',
                'Welcome to {app_name}, {first_name}! 🎉',
                TemplateType::WELCOME,
                'Modern blue-themed welcome email',
                $this->getModernWelcomeBlue()
            ),
            $this->createTemplate(
                'Welcome with Video',
                'Hi {first_name}, Watch Our Welcome Video!',
                TemplateType::WELCOME,
                'Welcome email with embedded video',
                $this->getWelcomeWithVideo()
            ),
            $this->createTemplate(
                'Minimalist Welcome',
                'Welcome {first_name} - Let\'s Get Started',
                TemplateType::WELCOME,
                'Clean minimalist welcome design',
                $this->getMinimalistWelcome()
            ),
            $this->createTemplate(
                'Welcome with Checklist',
                'Your Getting Started Guide, {first_name}',
                TemplateType::WELCOME,
                'Welcome with actionable checklist',
                $this->getWelcomeChecklist()
            ),
            $this->createTemplate(
                'Bold Welcome',
                '{first_name}, You\'re In! Welcome Aboard 🚀',
                TemplateType::WELCOME,
                'Bold and energetic welcome email',
                $this->getBoldWelcome()
            ),
        ];
    }

    private function getMarketingTemplates(): array
    {
        return [
            $this->createTemplate(
                'Flash Sale - Urgent',
                '⚡ Flash Sale! {first_name}, 24 Hours Only',
                TemplateType::PROMOTIONAL,
                'Urgent flash sale template',
                $this->getFlashSale()
            ),
            $this->createTemplate(
                'Product Launch',
                'Introducing Our Latest: Mastering Success in 2025',
                TemplateType::PROMOTIONAL,
                'Product launch announcement',
                $this->getProductLaunch()
            ),
            $this->createTemplate(
                'Limited Offer',
                '{first_name}, Exclusive Offer Expires Soon',
                TemplateType::PROMOTIONAL,
                'Limited time offer template',
                $this->getLimitedOffer()
            ),
            $this->createTemplate(
                'Black Friday Special',
                'BLACK FRIDAY: Up to 70% Off!',
                TemplateType::PROMOTIONAL,
                'Black Friday sale template',
                $this->getBlackFriday()
            ),
            $this->createTemplate(
                'Cyber Monday',
                'Cyber Monday Deals Start NOW!',
                TemplateType::PROMOTIONAL,
                'Cyber Monday template',
                $this->getCyberMonday()
            ),
        ];
    }

    private function getTransactionalTemplates(): array
    {
        return [
            $this->createTemplate(
                'Order Confirmation',
                'Order Confirmed - #1010123',
                TemplateType::TRANSACTIONAL,
                'Order confirmation email',
                $this->getOrderConfirmation()
            ),
            $this->createTemplate(
                'Shipping Notification',
                'Your Order Has Shipped! 📦',
                TemplateType::TRANSACTIONAL,
                'Shipping notification',
                $this->getShippingNotification()
            ),
            $this->createTemplate(
                'Invoice',
                'Invoice #1010123 from {app_name}',
                TemplateType::TRANSACTIONAL,
                'Invoice template',
                $this->getInvoice()
            ),
            $this->createTemplate(
                'Receipt',
                'Your Receipt from {app_name}',
                TemplateType::TRANSACTIONAL,
                'Payment receipt',
                $this->getReceipt()
            ),
        ];
    }

    private function getNewsletterTemplates(): array
    {
        return [
            $this->createTemplate(
                'Weekly Digest',
                '📰 Your Weekly Update - {date}',
                TemplateType::NEWSLETTER,
                'Weekly newsletter digest',
                $this->getWeeklyDigest()
            ),
            $this->createTemplate(
                'Blog Roundup',
                'Top Articles This Month',
                TemplateType::NEWSLETTER,
                'Blog posts roundup',
                $this->getBlogRoundup()
            ),
            $this->createTemplate(
                'Industry News',
                'Industry Insights for Industry 2025',
                TemplateType::NEWSLETTER,
                'Industry news newsletter',
                $this->getIndustryNews()
            ),
            $this->createTemplate(
                'Company Newsletter',
                'Company News & Updates - {month}',
                TemplateType::NEWSLETTER,
                'Company newsletter',
                $this->getCompanyNewsletter()
            ),
            $this->createTemplate(
                'Tips & Tricks',
                'Weekly Tips: Boost Your Productivity',
                TemplateType::NEWSLETTER,
                'Tips and tricks newsletter',
                $this->getTipsNewsletter()
            ),
        ];
    }

    private function getEventTemplates(): array
    {
        return [
            $this->createTemplate(
                'Webinar Invitation',
                'You\'re Invited: Mastering Success in {year}',
                TemplateType::REMINDER,
                'Webinar invitation',
                $this->getWebinarInvitation()
            ),
            $this->createTemplate(
                'Event Reminder',
                'Tomorrow: Mastering Success in {year} Starts at {time}',
                TemplateType::REMINDER,
                'Event reminder',
                $this->getEventReminder()
            ),
            $this->createTemplate(
                'Conference Invite',
                'Join Us at the Annual Conference {year}',
                TemplateType::REMINDER,
                'Conference invitation',
                $this->getConferenceInvite()
            ),
            $this->createTemplate(
                'Virtual Event',
                'Virtual Event: Mastering Success in {year} - Register Now',
                TemplateType::REMINDER,
                'Virtual event template',
                $this->getVirtualEvent()
            ),
            $this->createTemplate(
                'Event Thank You',
                'Thank You for Attending Mastering Success in {year}',
                TemplateType::FOLLOW_UP,
                'Post-event thank you',
                $this->getEventThankYou()
            ),
        ];
    }

    private function getEcommerceTemplates(): array
    {
        return [
            $this->createTemplate(
                'Cart Abandonment',
                '{first_name}, You Left Something Behind! 🛒',
                TemplateType::REMINDER,
                'Abandoned cart recovery',
                $this->getCartAbandonment()
            ),
            $this->createTemplate(
                'Product Recommendation',
                'Based on Your Interests, {first_name}',
                TemplateType::PROMOTIONAL,
                'Personalized recommendations',
                $this->getProductRecommendation()
            ),
            $this->createTemplate(
                'Back in Stock',
                'Product XYZ is Back in Stock!',
                TemplateType::PROMOTIONAL,
                'Back in stock notification',
                $this->getBackInStock()
            ),
            $this->createTemplate(
                'Review Request',
                'How Was Your Purchase, {first_name}?',
                TemplateType::FOLLOW_UP,
                'Product review request',
                $this->getReviewRequest()
            ),
            $this->createTemplate(
                'Birthday Discount',
                'Happy Birthday {first_name}! 🎂',
                TemplateType::PROMOTIONAL,
                'Birthday special offer',
                $this->getBirthdayDiscount()
            ),
        ];
    }

    private function createTemplate(string $name, string $subject, TemplateType $type, string $description, string $html, $active = false, $deletable = true): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $name,
            'subject' => $subject,
            'body_html' => $html,
            'type' => $type,
            'description' => $description,
            'is_active' => $active,
            'is_deleteable' => $deletable,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function getPasswordReset(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background: #635bff; border-radius: 10px;">
                            {site_icon_image}
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">Password Reset Request</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin: 0 0 20px;">
                                Hello <strong>{full_name}</strong>,
                            </p>
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin: 0 0 20px;">
                                We received a request to reset your password for your <strong>{app_name}</strong> account.
                                If you didn't make this request, you can safely ignore this email.
                            </p>
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin: 0 0 30px;">
                                To reset your password, click the button below:
                            </p>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{reset_url}" style="display: inline-block; padding: 16px 40px; background: #635bff; color: #ffffff; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                                    Reset My Password
                                </a>
                            </div>
                            <div style="background-color: #f8f9fa; border-left: 4px solid #ffc107; padding: 20px; margin: 30px 0; border-radius: 4px;">
                                <p style="font-size: 14px; color: #856404; margin: 0; line-height: 1.6;">
                                    <strong>⚠️ Important:</strong> This password reset link will expire in <strong>{expiry_time}</strong>.
                                    If the link expires, you'll need to request a new password reset.
                                </p>
                            </div>
                            <p style="font-size: 14px; color: #666666; line-height: 1.6; margin: 30px 0 20px;">
                                If the button above doesn't work, copy and paste this URL into your browser:
                            </p>
                            <p style="font-size: 13px; color: #667eea; word-break: break-all; background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 0 0 30px;">
                                {reset_url}
                            </p>
                            <div style="border-top: 2px solid #e9ecef; padding-top: 30px; margin-top: 30px;">
                                <p style="font-size: 14px; color: #666666; line-height: 1.6; margin: 0 0 10px;">
                                    <strong>Security Tips:</strong>
                                </p>
                                <ul style="font-size: 14px; color: #666666; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>Never share your password with anyone</li>
                                    <li>Use a strong, unique password</li>
                                    <li>Enable two-factor authentication if available</li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px; background-color: #f8f9fa; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="font-size: 14px; color: #666666; margin: 0 0 10px;">
                                If you didn't request this password reset, please contact our support team immediately.
                            </p>
                            <p style="font-size: 13px; color: #999999; margin: 0;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getModernWelcomeBlue(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: #635bff; padding: 60px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 36px; font-weight: 700;">Welcome to {app_name}! 🎉</h1>
                            <p style="color: #ffffff; margin: 20px 0 0 0; font-size: 18px; opacity: 0.9;">We're thrilled to have you, {first_name}</p>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 50px 40px;">
                            <p style="font-size: 16px; line-height: 1.8; color: #333333; margin: 0 0 20px 0;">Hi {first_name},</p>
                            <p style="font-size: 16px; line-height: 1.8; color: #333333; margin: 0 0 30px 0;">
                                Thank you for joining {app_name}! Your journey towards success starts now.
                            </p>
                            
                            <!-- Feature Cards -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td style="padding: 20px; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #333;">✨ Personalized Experience</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Tailored solutions designed specifically for your needs.</p>
                                    </td>
                                </tr>
                                <tr><td style="height: 15px;"></td></tr>
                                <tr>
                                    <td style="padding: 20px; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #764ba2;">
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #333;">🎯 Expert Support</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">24/7 dedicated support team ready to help you succeed.</p>
                                    </td>
                                </tr>
                                <tr><td style="height: 15px;"></td></tr>
                                <tr>
                                    <td style="padding: 20px; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #333;">🚀 Continuous Innovation</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Regular updates and new features to keep you ahead.</p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background: #635bff; color: #ffffff; padding: 16px 50px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">Get Started</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 16px; line-height: 1.8; color: #333333; margin: 30px 0 0 0;">
                                If you have any questions, we're here to help!
                            </p>
                            <p style="font-size: 16px; line-height: 1.8; color: #333333; margin: 10px 0 0 0;">
                                Best regards,<br>
                                <strong>The {app_name} Team</strong>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #999999; line-height: 1.6;">
                                © {year} {app_name}. All rights reserved.<br>
                                You're receiving this because you signed up for {app_name}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getWelcomeWithVideo(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f0f0f0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0f0f0; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px;">
                            <h1 style="color: #333; margin: 0 0 20px 0; font-size: 28px;">Welcome {first_name}! 👋</h1>
                            <p style="font-size: 16px; line-height: 1.6; color: #666; margin: 0 0 30px 0;">
                                Watch our quick welcome video to get started:
                            </p>
                            
                            <!-- Video Placeholder -->
                            <div style="position: relative; background-color: #000; border-radius: 8px; overflow: hidden; margin: 30px 0;">
                                <video width="100%" controls style="max-width: 100%; display: block;">
                                    <source src="https://example.com/welcome-video.mp4" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            
                            <p style="font-size: 16px; line-height: 1.6; color: #666; margin: 30px 0;">
                                Can't watch the video? No problem! Here's what you need to know:
                            </p>
                            
                            <ul style="font-size: 16px; line-height: 2; color: #666;">
                                <li>Complete your profile</li>
                                <li>Explore our features</li>
                                <li>Connect with our community</li>
                                <li>Start achieving your goals</li>
                            </ul>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #007bff; color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;">Complete Your Profile</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 14px; color: #999; margin: 30px 0 0 0; text-align: center;">
                                The {app_name} Team
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getMinimalistWelcome(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #ffffff;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 60px 20px;">
        <tr>
            <td align="center">
                <table width="500" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding: 0 20px;">
                            <h1 style="font-size: 32px; font-weight: 300; color: #000; margin: 0 0 40px 0; letter-spacing: -1px;">Welcome, {first_name}</h1>
                            
                            <p style="font-size: 18px; line-height: 1.8; color: #333; margin: 0 0 30px 0;">
                                We're glad you're here.
                            </p>
                            
                            <div style="height: 1px; background-color: #e0e0e0; margin: 40px 0;"></div>
                            
                            <p style="font-size: 16px; line-height: 1.8; color: #666; margin: 0 0 20px 0;">
                                {app_name} is designed to help you succeed. Let's get started.
                            </p>
                            
                            <p style="margin: 40px 0;">
                                <a href="#" style="color: #000; text-decoration: none; font-size: 16px; border-bottom: 2px solid #000; padding-bottom: 2px;">Get Started →</a>
                            </p>
                            
                            <div style="height: 1px; background-color: #e0e0e0; margin: 60px 0 40px 0;"></div>
                            
                            <p style="font-size: 14px; color: #999; margin: 0;">
                                {app_name}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    // Continue with other template methods... (due to length, showing pattern)
    // Each method follows similar structure with unique HTML design

    private function getWelcomeChecklist(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 50px 40px;">
                            <h1 style="color: #1a1a1a; margin: 0 0 15px 0; font-size: 32px; font-weight: 700;">Welcome {first_name}! 🎉</h1>
                            <p style="font-size: 18px; line-height: 1.6; color: #666; margin: 0 0 30px 0;">
                                Let's get you started with {app_name}. Here's your quick setup checklist:
                            </p>

                            <!-- Checklist Items -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td style="padding: 20px; background-color: #f9fafb; border-radius: 8px; margin-bottom: 15px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" style="vertical-align: top;">
                                                    <span style="display: inline-block; width: 28px; height: 28px; background-color: #10b981; border-radius: 50%; text-align: center; line-height: 28px; color: #fff; font-size: 16px;">✓</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1a1a1a;">Step 1: Complete Your Profile</h3>
                                                    <p style="margin: 0; font-size: 15px; color: #666; line-height: 1.6;">Add your details and preferences to personalize your experience.</p>
                                                    <a href="#" style="display: inline-block; margin-top: 10px; color: #3b82f6; text-decoration: none; font-weight: 600;">Complete Profile →</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="height: 15px;"></td></tr>
                                <tr>
                                    <td style="padding: 20px; background-color: #f9fafb; border-radius: 8px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" style="vertical-align: top;">
                                                    <span style="display: inline-block; width: 28px; height: 28px; background-color: #e5e7eb; border-radius: 50%; text-align: center; line-height: 28px; color: #9ca3af; font-size: 16px;">2</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1a1a1a;">Step 2: Explore Features</h3>
                                                    <p style="margin: 0; font-size: 15px; color: #666; line-height: 1.6;">Take a tour of our powerful features and tools.</p>
                                                    <a href="#" style="display: inline-block; margin-top: 10px; color: #3b82f6; text-decoration: none; font-weight: 600;">Start Tour →</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="height: 15px;"></td></tr>
                                <tr>
                                    <td style="padding: 20px; background-color: #f9fafb; border-radius: 8px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" style="vertical-align: top;">
                                                    <span style="display: inline-block; width: 28px; height: 28px; background-color: #e5e7eb; border-radius: 50%; text-align: center; line-height: 28px; color: #9ca3af; font-size: 16px;">3</span>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1a1a1a;">Step 3: Invite Your Team</h3>
                                                    <p style="margin: 0; font-size: 15px; color: #666; line-height: 1.6;">Collaborate better by inviting team members.</p>
                                                    <a href="#" style="display: inline-block; margin-top: 10px; color: #3b82f6; text-decoration: none; font-weight: 600;">Send Invites →</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 15px; line-height: 1.8; color: #666; margin: 30px 0 0 0;">
                                Need help? Our support team is ready to assist you 24/7.
                            </p>
                            <p style="font-size: 16px; color: #1a1a1a; margin: 20px 0 0 0;">
                                Best regards,<br>
                                <strong>The {app_name} Team</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 13px; color: #9ca3af; line-height: 1.6;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getBoldWelcome(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #000000;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #000000; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #000000;">
                    <tr>
                        <td style="padding: 60px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0 0 20px 0; font-size: 56px; font-weight: 900; letter-spacing: -2px; line-height: 1.1;">YOU'RE IN! 🚀</h1>
                            <p style="color: #a0a0a0; margin: 0; font-size: 24px; font-weight: 400; letter-spacing: 0.5px;">WELCOME {first_name}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 40px 50px 40px;">
                            <div style="height: 4px; background: #635bff; margin: 0 0 40px 0;"></div>

                            <p style="font-size: 20px; line-height: 1.7; color: #ffffff; margin: 0 0 30px 0; text-align: center;">
                                This is where your journey to <strong style="color: #ff0080;">success</strong> begins.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td style="padding: 30px; background: #635bff; border-radius: 12px; border: 2px solid #333;">
                                        <h2 style="color: #ffffff; margin: 0 0 15px 0; font-size: 28px; font-weight: 700;">WHAT'S NEXT?</h2>
                                        <p style="color: #d0d0d0; margin: 0; font-size: 17px; line-height: 1.8;">
                                            → Set up your dashboard<br>
                                            → Customize your preferences<br>
                                            → Start crushing your goals<br>
                                            → Join our community
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 50px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background: #635bff; color: #ffffff; padding: 20px 60px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 8px 30px rgba(255, 0, 128, 0.4);">LET'S GO!</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 16px; line-height: 1.8; color: #a0a0a0; margin: 40px 0 0 0; text-align: center;">
                                Questions? Hit reply. We're here for you.<br>
                                <strong style="color: #ffffff;">— Team {app_name}</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px; text-align: center; border-top: 1px solid #333;">
                            <p style="margin: 0; font-size: 12px; color: #666; line-height: 1.6;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getFlashSale(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #fff3cd;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff3cd; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 4px solid #ff6b6b; border-radius: 8px;">
                    <tr>
                        <td style="background: #635bff; padding: 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0 0 10px 0; font-size: 48px; font-weight: 900; text-transform: uppercase;">⚡ FLASH SALE ⚡</h1>
                            <p style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">24 HOURS ONLY!</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <p style="font-size: 18px; color: #333; margin: 0 0 30px 0; text-align: center;">
                                Hi {first_name}, this is not a drill! 🚨
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff3cd; border-radius: 8px; padding: 30px; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <p style="margin: 0 0 10px 0; font-size: 18px; color: #856404; font-weight: 600;">SAVE UP TO</p>
                                        <h2 style="margin: 0; font-size: 72px; font-weight: 900; color: #ff6b6b; line-height: 1;">50%</h2>
                                        <p style="margin: 10px 0 0 0; font-size: 18px; color: #856404; font-weight: 600;">OFF EVERYTHING</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #f8f9fa; border-left: 4px solid #ff6b6b; padding: 20px; margin: 30px 0;">
                                <p style="margin: 0; font-size: 16px; color: #333; line-height: 1.8;">
                                    ⏰ <strong>Time Remaining:</strong> 23:45:12<br>
                                    🎯 <strong>Use Code:</strong> <span style="background-color: #ff6b6b; color: #fff; padding: 5px 15px; border-radius: 4px; font-weight: 700;">FLASH50</span><br>
                                    🚚 <strong>Free Shipping:</strong> On all orders over $50
                                </p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #ff6b6b; color: #ffffff; padding: 20px 50px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 700; text-transform: uppercase; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);">Shop Now</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #666; margin: 30px 0 0 0; text-align: center; line-height: 1.6;">
                                Hurry! Sale ends in 24 hours or while supplies last.<br>
                                No code needed on already discounted items.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;">
                            <p style="margin: 0; font-size: 12px; color: #999;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getProductLaunch(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f0f0f0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0f0f0; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden;">
                    <tr>
                        <td style="padding: 50px 40px; text-align: center; background: #635bff;">
                            <p style="color: #ffffff; margin: 0 0 10px 0; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px;">New Release</p>
                            <h1 style="color: #ffffff; margin: 0; font-size: 42px; font-weight: 700; line-height: 1.2;">Introducing<br>Product XYZ</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0;">
                            <img src="https://placehold.co/600x400/667eea/ffffff?text=Product+Image" alt="Product" style="width: 100%; height: auto; display: block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 50px 40px;">
                            <p style="font-size: 20px; line-height: 1.6; color: #333; margin: 0 0 30px 0; text-align: center; font-weight: 600;">
                                The future is here. Discover what's possible.
                            </p>

                            <p style="font-size: 16px; line-height: 1.8; color: #666; margin: 0 0 30px 0;">
                                We're thrilled to announce our latest innovation: Product XYZ. Designed with you in mind, it combines cutting-edge technology with intuitive design to deliver an unparalleled experience.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td width="50%" style="padding: 20px; vertical-align: top;">
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #333;">✨ Advanced Features</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Next-generation technology that adapts to your needs.</p>
                                    </td>
                                    <td width="50%" style="padding: 20px; vertical-align: top;">
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #333;">🎯 User-Friendly</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Intuitive design that anyone can master instantly.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding: 20px; vertical-align: top;">
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #333;">⚡ Lightning Fast</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Performance that keeps up with your ambitions.</p>
                                    </td>
                                    <td width="50%" style="padding: 20px; vertical-align: top;">
                                        <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #333;">🔒 Secure</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Enterprise-grade security for peace of mind.</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 30px; margin: 30px 0; text-align: center;">
                                <p style="margin: 0 0 5px 0; font-size: 14px; color: #666; text-transform: uppercase; letter-spacing: 1px;">Launch Special</p>
                                <h2 style="margin: 0 0 5px 0; font-size: 36px; color: #667eea; font-weight: 700;">20% OFF</h2>
                                <p style="margin: 0; font-size: 14px; color: #666;">Limited time offer for early adopters</p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background: #635bff; color: #ffffff; padding: 18px 50px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">Order Now</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #999; line-height: 1.6;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getLimitedOffer(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #fef3c7;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef3c7; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <div style="background-color: #f59e0b; color: #ffffff; padding: 10px 20px; border-radius: 20px; display: inline-block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Exclusive Offer</div>
                            <h1 style="color: #1a1a1a; margin: 20px 0 15px 0; font-size: 36px; font-weight: 700;">{first_name}, This Won't Last Long</h1>
                            <p style="font-size: 18px; line-height: 1.6; color: #666; margin: 0 0 30px 0;">
                                A special offer just for you – but only for the next 48 hours.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background: #635bff; border-radius: 12px; padding: 40px; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <p style="margin: 0 0 15px 0; color: #ffffff; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Your Exclusive Discount</p>
                                        <h2 style="margin: 0; color: #ffffff; font-size: 64px; font-weight: 900; line-height: 1;">35%</h2>
                                        <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 18px; font-weight: 600;">OFF YOUR NEXT PURCHASE</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="text-align: center; padding: 30px; background-color: #fffbeb; border-radius: 8px; border: 2px dashed #f59e0b; margin: 30px 0;">
                                <p style="margin: 0 0 10px 0; font-size: 14px; color: #92400e; font-weight: 600; text-transform: uppercase;">Your Promo Code</p>
                                <p style="margin: 0; font-size: 32px; color: #f59e0b; font-weight: 900; letter-spacing: 3px; font-family: 'Courier New', monospace;">SAVE35</p>
                                <p style="margin: 15px 0 0 0; font-size: 13px; color: #92400e;">Copy this code at checkout</p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #1a1a1a; color: #ffffff; padding: 18px 50px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 700; text-transform: uppercase;">Shop Now</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 30px 0;">
                                <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.8;">
                                    ⏰ <strong>Offer expires:</strong> December 15, 2025 at 11:59 PM<br>
                                    ✓ <strong>Valid on:</strong> All products (some exclusions apply)<br>
                                    ✓ <strong>Free shipping:</strong> On orders over $75
                                </p>
                            </div>

                            <p style="font-size: 14px; color: #666; margin: 20px 0 0 0; text-align: center; line-height: 1.6;">
                                Questions? Contact us at support@{from_email}.com
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af; line-height: 1.6;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getBlackFriday(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #000000;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #000000; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #1a1a1a; border: 3px solid #ff0000;">
                    <tr>
                        <td style="background-color: #ff0000; padding: 30px; text-align: center;">
                            <h1 style="color: #000000; margin: 0; font-size: 52px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px;">BLACK FRIDAY</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 50px 40px; text-align: center;">
                            <p style="color: #ffffff; margin: 0 0 30px 0; font-size: 24px; font-weight: 700; text-transform: uppercase;">The Biggest Sale of the Year</p>

                            <div style="background: #635bff; padding: 40px; border-radius: 8px; margin: 30px 0;">
                                <h2 style="color: #ffffff; margin: 0 0 15px 0; font-size: 28px; font-weight: 700;">UP TO</h2>
                                <p style="color: #ffffff; margin: 0; font-size: 96px; font-weight: 900; line-height: 1;">70%</p>
                                <h3 style="color: #ffffff; margin: 15px 0 0 0; font-size: 28px; font-weight: 700;">OFF</h3>
                            </div>

                            <p style="color: #cccccc; margin: 30px 0; font-size: 18px; line-height: 1.6;">
                                🔥 Everything on sale<br>
                                🚚 Free worldwide shipping<br>
                                ⚡ Limited quantities available<br>
                                🎁 Extra 10% off with code: BF70
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #ff0000; color: #ffffff; padding: 22px 60px; text-decoration: none; border-radius: 4px; font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; box-shadow: 0 8px 20px rgba(255, 0, 0, 0.5);">SHOP NOW</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #2a2a2a; border: 2px solid #ff0000; padding: 25px; margin: 40px 0; border-radius: 8px;">
                                <p style="color: #ffffff; margin: 0; font-size: 16px; font-weight: 700;">⏰ SALE ENDS IN:</p>
                                <p style="color: #ff0000; margin: 10px 0 0 0; font-size: 32px; font-weight: 900; letter-spacing: 2px;">23:59:47</p>
                            </div>

                            <p style="color: #999999; margin: 30px 0 0 0; font-size: 13px; line-height: 1.6;">
                                *Discounts applied automatically. Some exclusions may apply.<br>
                                Sale ends November 24, 2025 at midnight.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0d0d0d; padding: 25px; text-align: center; border-top: 1px solid #333;">
                            <p style="margin: 0; font-size: 12px; color: #666;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getCyberMonday(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0a192f;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #0a192f; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: #635bff; border: 2px solid #00d4ff;">
                    <tr>
                        <td style="padding: 50px 40px; text-align: center;">
                            <h1 style="color: #00d4ff; margin: 0 0 10px 0; font-size: 48px; font-weight: 900; text-transform: uppercase; letter-spacing: 3px; text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);">CYBER MONDAY</h1>
                            <p style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 600; text-transform: uppercase;">Deals Start NOW!</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 40px 50px 40px;">
                            <div style="background: #635bff; padding: 40px; border-radius: 12px; text-align: center; margin: 30px 0; box-shadow: 0 10px 40px rgba(0, 212, 255, 0.3);">
                                <p style="color: #0a192f; margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">DOORBUSTER DEAL</p>
                                <h2 style="color: #0a192f; margin: 0; font-size: 72px; font-weight: 900; line-height: 1;">60%</h2>
                                <p style="color: #0a192f; margin: 10px 0 0 0; font-size: 18px; font-weight: 700;">OFF SITEWIDE</p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td width="50%" style="padding: 15px;">
                                        <div style="background-color: #1a2332; border: 2px solid #00d4ff; border-radius: 8px; padding: 25px; text-align: center;">
                                            <p style="color: #00d4ff; margin: 0 0 10px 0; font-size: 48px; font-weight: 900;">40%</p>
                                            <p style="color: #ffffff; margin: 0; font-size: 14px;">Electronics</p>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding: 15px;">
                                        <div style="background-color: #1a2332; border: 2px solid #00d4ff; border-radius: 8px; padding: 25px; text-align: center;">
                                            <p style="color: #00d4ff; margin: 0 0 10px 0; font-size: 48px; font-weight: 900;">50%</p>
                                            <p style="color: #ffffff; margin: 0; font-size: 14px;">Software</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding: 15px;">
                                        <div style="background-color: #1a2332; border: 2px solid #00d4ff; border-radius: 8px; padding: 25px; text-align: center;">
                                            <p style="color: #00d4ff; margin: 0 0 10px 0; font-size: 48px; font-weight: 900;">45%</p>
                                            <p style="color: #ffffff; margin: 0; font-size: 14px;">Accessories</p>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding: 15px;">
                                        <div style="background-color: #1a2332; border: 2px solid #00d4ff; border-radius: 8px; padding: 25px; text-align: center;">
                                            <p style="color: #00d4ff; margin: 0 0 10px 0; font-size: 48px; font-weight: 900;">35%</p>
                                            <p style="color: #ffffff; margin: 0; font-size: 14px;">Services</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #1a2332; border-left: 4px solid #00d4ff; padding: 25px; margin: 30px 0;">
                                <p style="color: #ffffff; margin: 0; font-size: 15px; line-height: 1.8;">
                                    💻 Tech deals you can't miss<br>
                                    🎯 Early access for subscribers<br>
                                    🚀 Flash deals every hour<br>
                                    📦 Free express shipping
                                </p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background: #635bff; color: #0a192f; padding: 20px 50px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 900; text-transform: uppercase; box-shadow: 0 8px 25px rgba(0, 212, 255, 0.4);">Shop Cyber Deals</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #7a8a99; margin: 30px 0 0 0; font-size: 13px; text-align: center; line-height: 1.6;">
                                Sale ends Tuesday at 11:59 PM EST<br>
                                While supplies last. No rain checks.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0a192f; padding: 25px; text-align: center; border-top: 1px solid #1a2332;">
                            <p style="margin: 0; font-size: 12px; color: #7a8a99;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getOrderConfirmation(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <div style="text-align: center; margin-bottom: 30px;">
                                <div style="display: inline-block; width: 60px; height: 60px; background-color: #10b981; border-radius: 50%; text-align: center; line-height: 60px;">
                                    <span style="color: #ffffff; font-size: 30px;">✓</span>
                                </div>
                            </div>
                            <h1 style="color: #1a1a1a; margin: 0 0 10px 0; font-size: 28px; font-weight: 700; text-align: center;">Order Confirmed!</h1>
                            <p style="font-size: 16px; color: #666; margin: 0 0 30px 0; text-align: center;">
                                Thank you for your order. We'll send you a shipping confirmation email as soon as your order ships.
                            </p>

                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 30px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 8px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #666;">Order Number:</p>
                                        </td>
                                        <td style="padding: 8px 0; text-align: right;">
                                            <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">#123456789</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #666;">Order Date:</p>
                                        </td>
                                        <td style="padding: 8px 0; text-align: right;">
                                            <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">November 14, 2025</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #666;">Order Total:</p>
                                        </td>
                                        <td style="padding: 8px 0; text-align: right;">
                                            <p style="margin: 0; font-size: 18px; color: #1a1a1a; font-weight: 700;">$127.50</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <h2 style="color: #1a1a1a; margin: 30px 0 20px 0; font-size: 20px; font-weight: 600;">Order Details</h2>

                            <table width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                                <tr>
                                    <td style="padding: 20px 0; border-bottom: 1px solid #f3f4f6;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="80" style="vertical-align: top;">
                                                    <img src="https://placehold.co/80x80/e5e7eb/999999?text=Product" alt="Product" style="width: 80px; height: 80px; border-radius: 8px;">
                                                </td>
                                                <td style="padding-left: 20px; vertical-align: top;">
                                                    <p style="margin: 0 0 5px 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">Product Name</p>
                                                    <p style="margin: 0; font-size: 14px; color: #666;">Size: Medium, Color: Blue</p>
                                                </td>
                                                <td style="text-align: right; vertical-align: top;">
                                                    <p style="margin: 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">$49.99</p>
                                                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Qty: 1</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td width="50%" style="padding-right: 15px; vertical-align: top;">
                                        <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">Shipping Address</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.8;">
                                            {first_name} {last_name}<br>
                                            123 Main Street<br>
                                            Apartment 4B<br>
                                            New York, NY 10001<br>
                                            United States
                                        </p>
                                    </td>
                                    <td width="50%" style="padding-left: 15px; vertical-align: top;">
                                        <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">Payment Method</h3>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.8;">
                                            Visa ending in 4242<br>
                                            Billing address same as shipping
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 14px 40px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: 600;">View Order Status</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #666; margin: 30px 0 0 0; text-align: center; line-height: 1.6;">
                                Questions about your order?<br>
                                Contact us at <a href="mailto:support@{from_email}.com" style="color: #3b82f6; text-decoration: none;">support@{from_email}.com</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af; line-height: 1.6;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getShippingNotification(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px;">
                            <div style="text-align: center; margin-bottom: 30px;">
                                <span style="font-size: 60px;">📦</span>
                            </div>
                            <h1 style="color: #1a1a1a; margin: 0 0 10px 0; font-size: 28px; font-weight: 700; text-align: center;">Your Order Has Shipped!</h1>
                            <p style="font-size: 16px; color: #666; margin: 0 0 30px 0; text-align: center;">
                                Good news {first_name}! Your package is on its way.
                            </p>

                            <div style="background: #635bff; border-radius: 12px; padding: 30px; margin: 30px 0; text-align: center;">
                                <p style="color: #ffffff; margin: 0 0 10px 0; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Tracking Number</p>
                                <p style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 2px; font-family: 'Courier New', monospace;">1Z999AA10123456784</p>
                            </div>

                            <div style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 5px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #666;">Carrier:</p>
                                        </td>
                                        <td style="padding: 5px 0; text-align: right;">
                                            <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">UPS Ground</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #666;">Estimated Delivery:</p>
                                        </td>
                                        <td style="padding: 5px 0; text-align: right;">
                                            <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">November 18-20, 2025</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #666;">Ship To:</p>
                                        </td>
                                        <td style="padding: 5px 0; text-align: right;">
                                            <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">{first_name} {last_name}</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 14px 40px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: 600;">Track Your Package</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin: 30px 0;">
                                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">What's in your package:</h3>
                                <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.8;">
                                    • Product Name - Qty: 1<br>
                                    • Another Product - Qty: 2
                                </p>
                            </div>

                            <p style="font-size: 14px; color: #666; margin: 30px 0 0 0; text-align: center; line-height: 1.8;">
                                We'll send you another email when your package is delivered.<br>
                                <a href="#" style="color: #3b82f6; text-decoration: none;">Manage your orders</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getInvoice(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="650" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; font-size: 32px; color: #1a1a1a; font-weight: 700;">INVOICE</h1>
                                        <p style="margin: 5px 0 0 0; font-size: 16px; color: #666;">#123456789</p>
                                    </td>
                                    <td style="text-align: right;">
                                        <p style="margin: 0; font-size: 24px; color: #1a1a1a; font-weight: 700;">{app_name}</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="height: 2px; background-color: #e5e7eb; margin: 30px 0;"></div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td width="50%" style="vertical-align: top;">
                                        <p style="margin: 0 0 10px 0; font-size: 12px; color: #666; text-transform: uppercase; font-weight: 600;">Billed To:</p>
                                        <p style="margin: 0; font-size: 15px; color: #1a1a1a; line-height: 1.8;">
                                            <strong>{first_name} {last_name}</strong><br>
                                            123 Main Street<br>
                                            New York, NY 10001<br>
                                            United States
                                        </p>
                                    </td>
                                    <td width="50%" style="vertical-align: top; text-align: right;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 5px 0; text-align: right;">
                                                    <p style="margin: 0; font-size: 14px; color: #666;">Invoice Date:</p>
                                                </td>
                                                <td style="padding: 5px 0 5px 20px; text-align: right;">
                                                    <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">Nov 14, 2025</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px 0; text-align: right;">
                                                    <p style="margin: 0; font-size: 14px; color: #666;">Due Date:</p>
                                                </td>
                                                <td style="padding: 5px 0 5px 20px; text-align: right;">
                                                    <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">Dec 14, 2025</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0; border: 1px solid #e5e7eb;">
                                <tr style="background-color: #f9fafb;">
                                    <td style="padding: 15px; border-bottom: 1px solid #e5e7eb;">
                                        <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; font-weight: 600;">Description</p>
                                    </td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e5e7eb; text-align: center;">
                                        <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; font-weight: 600;">Quantity</p>
                                    </td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                        <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; font-weight: 600;">Unit Price</p>
                                    </td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                        <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; font-weight: 600;">Total</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; border-bottom: 1px solid #e5e7eb;">
                                        <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">Professional Plan</p>
                                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">Monthly subscription</p>
                                    </td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e5e7eb; text-align: center;">
                                        <p style="margin: 0; font-size: 14px; color: #1a1a1a;">1</p>
                                    </td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                        <p style="margin: 0; font-size: 14px; color: #1a1a1a;">$99.00</p>
                                    </td>
                                    <td style="padding: 15px; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                        <p style="margin: 0; font-size: 14px; color: #1a1a1a; font-weight: 600;">$99.00</p>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td width="65%"></td>
                                    <td width="35%">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <p style="margin: 0; font-size: 14px; color: #666;">Subtotal:</p>
                                                </td>
                                                <td style="padding: 8px 0; text-align: right;">
                                                    <p style="margin: 0; font-size: 14px; color: #1a1a1a;">$99.00</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <p style="margin: 0; font-size: 14px; color: #666;">Tax (10%):</p>
                                                </td>
                                                <td style="padding: 8px 0; text-align: right;">
                                                    <p style="margin: 0; font-size: 14px; color: #1a1a1a;">$9.90</p>
                                                </td>
                                            </tr>
                                            <tr style="border-top: 2px solid #e5e7eb;">
                                                <td style="padding: 15px 0 0 0;">
                                                    <p style="margin: 0; font-size: 18px; color: #1a1a1a; font-weight: 700;">Total:</p>
                                                </td>
                                                <td style="padding: 15px 0 0 0; text-align: right;">
                                                    <p style="margin: 0; font-size: 18px; color: #1a1a1a; font-weight: 700;">$108.90</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0;">
                                <p style="margin: 0; font-size: 14px; color: #1e40af; line-height: 1.6;">
                                    <strong>Payment Terms:</strong> Payment is due within 30 days. Please include invoice number with payment.
                                </p>
                            </div>

                            <p style="font-size: 13px; color: #666; margin: 30px 0 0 0; text-align: center; line-height: 1.6;">
                                Thank you for your business!<br>
                                Questions? Email <a href="mailto:billing@{app_name}.com" style="color: #3b82f6; text-decoration: none;">billing@{app_name}.com</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getReceipt(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: 'Courier New', monospace; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="550" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px;">
                            <div style="text-align: center; border-bottom: 2px dashed #ddd; padding-bottom: 30px; margin-bottom: 30px;">
                                <h1 style="margin: 0 0 10px 0; font-size: 28px; color: #1a1a1a; font-weight: 700;">{app_name}</h1>
                                <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">
                                    123 Business Street<br>
                                    New York, NY 10001<br>
                                    Tel: (555) 123-4567
                                </p>
                            </div>

                            <div style="text-align: center; margin: 20px 0;">
                                <h2 style="margin: 0; font-size: 24px; color: #1a1a1a; font-weight: 700;">RECEIPT</h2>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0; font-size: 13px;">
                                <tr>
                                    <td style="padding: 5px 0;">Date:</td>
                                    <td style="padding: 5px 0; text-align: right; font-weight: 600;">Nov 14, 2025 02:35 PM</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;">Receipt #:</td>
                                    <td style="padding: 5px 0; text-align: right; font-weight: 600;">101010</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;">Cashier:</td>
                                    <td style="padding: 5px 0; text-align: right; font-weight: 600;">John Doe</td>
                                </tr>
                            </table>

                            <div style="border-top: 2px dashed #ddd; border-bottom: 2px dashed #ddd; padding: 20px 0; margin: 30px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
                                    <tr>
                                        <td style="padding: 8px 0;">
                                            <strong>Product Name</strong><br>
                                            <span style="color: #666; font-size: 12px;">SKU: ABC123</span>
                                        </td>
                                        <td style="padding: 8px 0; text-align: center;">x1</td>
                                        <td style="padding: 8px 0; text-align: right;"><strong>$49.99</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;">
                                            <strong>Another Product</strong><br>
                                            <span style="color: #666; font-size: 12px;">SKU: XYZ789</span>
                                        </td>
                                        <td style="padding: 8px 0; text-align: center;">x2</td>
                                        <td style="padding: 8px 0; text-align: right;"><strong>$79.98</strong></td>
                                    </tr>
                                </table>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 8px 0;">Subtotal:</td>
                                    <td style="padding: 8px 0; text-align: right;">$129.97</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;">Tax (8.5%):</td>
                                    <td style="padding: 8px 0; text-align: right;">$11.05</td>
                                </tr>
                                <tr style="font-size: 18px; font-weight: 700;">
                                    <td style="padding: 15px 0; border-top: 2px solid #000;">TOTAL:</td>
                                    <td style="padding: 15px 0; text-align: right; border-top: 2px solid #000;">$141.02</td>
                                </tr>
                            </table>

                            <div style="border-top: 2px dashed #ddd; padding-top: 20px; margin-top: 20px;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
                                    <tr>
                                        <td style="padding: 5px 0;">Payment Method:</td>
                                        <td style="padding: 5px 0; text-align: right; font-weight: 600;">Visa ****4242</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;">Authorization:</td>
                                        <td style="padding: 5px 0; text-align: right; font-weight: 600;">APR-12345</td>
                                    </tr>
                                </table>
                            </div>

                            <div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #f9fafb; border-radius: 8px;">
                                <p style="margin: 0; font-size: 12px; color: #666; line-height: 1.8;">
                                    Thank you for your purchase!<br>
                                    Return policy: 30 days with receipt<br>
                                    Questions? Visit our help center
                                </p>
                            </div>

                            <div style="text-align: center; border-top: 2px dashed #ddd; padding-top: 20px;">
                                <img src="https://placehold.co/200x50/ffffff/000000?text=BARCODE" alt="Barcode" style="max-width: 200px; height: auto;">
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getWeeklyDigest(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px 40px 30px 40px; border-bottom: 3px solid #3b82f6;">
                            <h1 style="color: #1a1a1a; margin: 0 0 10px 0; font-size: 32px; font-weight: 700;">📰 Weekly Digest</h1>
                            <p style="margin: 0; font-size: 16px; color: #666;">{date} • Your weekly roundup</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <p style="font-size: 16px; color: #333; margin: 0 0 30px 0; line-height: 1.6;">
                                Hi {first_name}, here's what happened this week at {app_name}.
                            </p>
                            <div style="margin: 30px 0; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
                                <img src="https://placehold.co/600x300/3b82f6/ffffff?text=Featured+Story" alt="Featured" style="width: 100%; height: auto; display: block;">
                                <div style="padding: 25px;">
                                    <span style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 15px;">Featured</span>
                                    <h2 style="margin: 0 0 10px 0; font-size: 22px; color: #1a1a1a; font-weight: 700;">The Future of Technology: What's Next</h2>
                                    <p style="margin: 0 0 15px 0; font-size: 15px; color: #666; line-height: 1.6;">Discover the latest trends and innovations shaping our industry...</p>
                                    <a href="#" style="color: #3b82f6; text-decoration: none; font-weight: 600; font-size: 15px;">Read More →</a>
                                </div>
                            </div>
                            <h3 style="margin: 40px 0 20px 0; font-size: 20px; color: #1a1a1a; font-weight: 700;">Top Stories</h3>
                            <div style="border-bottom: 1px solid #e5e7eb; padding: 20px 0;">
                                <h4 style="margin: 0 0 10px 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">
                                    <a href="#" style="color: #1a1a1a; text-decoration: none;">5 Tips to Boost Your Productivity</a>
                                </h4>
                                <p style="margin: 0 0 10px 0; font-size: 14px; color: #666; line-height: 1.6;">Learn how successful professionals manage their time...</p>
                                <span style="font-size: 13px; color: #999;">5 min read • Productivity</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getBlogRoundup(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Georgia, serif; background-color: #fafafa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fafafa; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff;">
                    <tr>
                        <td style="padding: 50px 40px; text-align: center; background-color: #1a1a1a;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 36px; font-weight: 300; letter-spacing: 2px;">TOP ARTICLES</h1>
                            <p style="color: #cccccc; margin: 10px 0 0 0; font-size: 16px;">This Month's Best Reads</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 50px 40px;">
                            <article style="margin-bottom: 40px; padding-bottom: 40px; border-bottom: 1px solid #e5e7eb;">
                                <img src="https://placehold.co/520x260/667eea/ffffff?text=Article+1" alt="Article" style="width: 100%; height: auto; border-radius: 4px; margin-bottom: 20px;">
                                <span style="font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px;">Technology • Nov 10, 2025</span>
                                <h2 style="margin: 15px 0 10px 0; font-size: 26px; color: #1a1a1a; font-weight: 600; line-height: 1.3;">The Rise of AI in Modern Business</h2>
                                <p style="font-size: 16px; color: #666; line-height: 1.8; margin: 0 0 15px 0;">Exploring how artificial intelligence is transforming the way companies operate and compete in today's digital landscape...</p>
                                <a href="#" style="color: #1a1a1a; text-decoration: none; font-weight: 600; border-bottom: 2px solid #1a1a1a; padding-bottom: 2px;">Continue Reading</a>
                            </article>

                            <article style="margin-bottom: 40px; padding-bottom: 40px; border-bottom: 1px solid #e5e7eb;">
                                <img src="https://placehold.co/520x260/764ba2/ffffff?text=Article+2" alt="Article" style="width: 100%; height: auto; border-radius: 4px; margin-bottom: 20px;">
                                <span style="font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px;">Leadership • Nov 8, 2025</span>
                                <h2 style="margin: 15px 0 10px 0; font-size: 26px; color: #1a1a1a; font-weight: 600; line-height: 1.3;">Building High-Performance Teams</h2>
                                <p style="font-size: 16px; color: #666; line-height: 1.8; margin: 0 0 15px 0;">Discover the secrets behind creating and maintaining teams that consistently deliver exceptional results...</p>
                                <a href="#" style="color: #1a1a1a; text-decoration: none; font-weight: 600; border-bottom: 2px solid #1a1a1a; padding-bottom: 2px;">Continue Reading</a>
                            </article>

                            <article style="margin-bottom: 40px;">
                                <img src="https://placehold.co/520x260/f59e0b/ffffff?text=Article+3" alt="Article" style="width: 100%; height: auto; border-radius: 4px; margin-bottom: 20px;">
                                <span style="font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px;">Innovation • Nov 5, 2025</span>
                                <h2 style="margin: 15px 0 10px 0; font-size: 26px; color: #1a1a1a; font-weight: 600; line-height: 1.3;">Design Thinking for Problem Solving</h2>
                                <p style="font-size: 16px; color: #666; line-height: 1.8; margin: 0 0 15px 0;">Learn how design thinking methodology can help you tackle complex challenges and drive innovation...</p>
                                <a href="#" style="color: #1a1a1a; text-decoration: none; font-weight: 600; border-bottom: 2px solid #1a1a1a; padding-bottom: 2px;">Continue Reading</a>
                            </article>

                            <div style="text-align: center; margin-top: 50px;">
                                <a href="#" style="display: inline-block; background-color: #1a1a1a; color: #ffffff; padding: 15px 40px; text-decoration: none; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">View All Articles</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #999;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getIndustryNews(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0f172a;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #0f172a; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #1e293b; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px; border-bottom: 2px solid #3b82f6;">
                            <h1 style="color: #ffffff; margin: 0 0 10px 0; font-size: 32px; font-weight: 700;">Industry Insights</h1>
                            <p style="color: #94a3b8; margin: 0; font-size: 16px;">Latest News & Trends</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <div style="background-color: #3b82f6; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
                                <h2 style="color: #ffffff; margin: 0 0 10px 0; font-size: 22px; font-weight: 700;">🔥 Trending Now</h2>
                                <p style="color: #e0e7ff; margin: 0; font-size: 18px; line-height: 1.6;">Market analysis shows 45% growth this quarter</p>
                            </div>

                            <div style="border-left: 4px solid #3b82f6; padding: 20px; margin: 25px 0; background-color: #334155;">
                                <h3 style="color: #ffffff; margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">Breaking: Major Industry Shift</h3>
                                <p style="color: #cbd5e1; margin: 0 0 10px 0; font-size: 15px; line-height: 1.6;">New regulations are reshaping the landscape...</p>
                                <a href="#" style="color: #60a5fa; text-decoration: none; font-weight: 600;">Read Full Story →</a>
                            </div>

                            <div style="border-left: 4px solid #10b981; padding: 20px; margin: 25px 0; background-color: #334155;">
                                <h3 style="color: #ffffff; margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">Market Update: Q4 Forecast</h3>
                                <p style="color: #cbd5e1; margin: 0 0 10px 0; font-size: 15px; line-height: 1.6;">Expert predictions for the upcoming quarter show promising trends...</p>
                                <a href="#" style="color: #34d399; text-decoration: none; font-weight: 600;">View Analysis →</a>
                            </div>

                            <div style="border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; background-color: #334155;">
                                <h3 style="color: #ffffff; margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">Technology Innovation Report</h3>
                                <p style="color: #cbd5e1; margin: 0 0 10px 0; font-size: 15px; line-height: 1.6;">Latest technological advancements disrupting traditional models...</p>
                                <a href="#" style="color: #fbbf24; text-decoration: none; font-weight: 600;">Download Report →</a>
                            </div>

                            <div style="background-color: #334155; border-radius: 8px; padding: 30px; margin: 30px 0;">
                                <h3 style="color: #ffffff; margin: 0 0 20px 0; font-size: 20px; font-weight: 700; text-align: center;">Quick Stats</h3>
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="text-align: center; padding: 15px;">
                                            <p style="margin: 0; font-size: 32px; color: #3b82f6; font-weight: 700;">+23%</p>
                                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #94a3b8;">Growth Rate</p>
                                        </td>
                                        <td style="text-align: center; padding: 15px;">
                                            <p style="margin: 0; font-size: 32px; color: #10b981; font-weight: 700;">$2.4B</p>
                                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #94a3b8;">Market Value</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 30px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getCompanyNewsletter(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px; background: #635bff;">
                            <h1 style="color: #ffffff; margin: 0 0 10px 0; font-size: 36px; font-weight: 700;">Company Update</h1>
                            <p style="color: #ffffff; margin: 0; font-size: 18px; opacity: 0.9;">{month} {year}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <p style="font-size: 16px; color: #333; margin: 0 0 30px 0; line-height: 1.6;">
                                Dear Team,
                            </p>

                            <h2 style="color: #1a1a1a; margin: 0 0 15px 0; font-size: 24px; font-weight: 700;">📢 Company News</h2>
                            <p style="font-size: 15px; color: #666; margin: 0 0 25px 0; line-height: 1.8;">
                                We're excited to share some major updates from this month. Our team has achieved remarkable milestones and we're proud of everyone's contributions.
                            </p>

                            <div style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 25px 0;">
                                <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #1e40af;">🎉 Major Achievement</h3>
                                <p style="margin: 0; font-size: 15px; color: #1e3a8a; line-height: 1.6;">We've reached 10,000 customers and secured $50M in Series B funding!</p>
                            </div>

                            <h2 style="color: #1a1a1a; margin: 30px 0 15px 0; font-size: 24px; font-weight: 700;">👥 Team Updates</h2>
                            <ul style="font-size: 15px; color: #666; line-height: 2; padding-left: 20px;">
                                <li>Welcome to our 5 new team members in Engineering</li>
                                <li>Sarah Johnson promoted to VP of Sales</li>
                                <li>New office opening in Austin, Texas</li>
                            </ul>

                            <h2 style="color: #1a1a1a; margin: 30px 0 15px 0; font-size: 24px; font-weight: 700;">🚀 Product Updates</h2>
                            <p style="font-size: 15px; color: #666; margin: 0 0 15px 0; line-height: 1.8;">
                                This month we launched three major features based on your feedback:
                            </p>
                            <ul style="font-size: 15px; color: #666; line-height: 2; padding-left: 20px; margin: 0 0 25px 0;">
                                <li>Advanced Analytics Dashboard</li>
                                <li>Mobile App Version 2.0</li>
                                <li>API Integration Platform</li>
                            </ul>

                            <div style="background-color: #fef3c7; border-radius: 8px; padding: 25px; margin: 30px 0;">
                                <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #92400e;">📅 Upcoming Events</h3>
                                <p style="margin: 0; font-size: 15px; color: #92400e; line-height: 1.8;">
                                    • Team Building Day - Dec 15<br>
                                    • Holiday Party - Dec 20<br>
                                    • Year-End All-Hands - Dec 22
                                </p>
                            </div>

                            <p style="font-size: 15px; color: #666; margin: 30px 0 0 0; line-height: 1.8;">
                                Thank you for your continued dedication and hard work!<br><br>
                                Best regards,<br>
                                <strong>Leadership Team</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. Internal Communication
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getTipsNewsletter(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #fffbeb;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fffbeb; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px; text-align: center; background-color: #fbbf24; border-radius: 12px 12px 0 0;">
                            <span style="font-size: 48px;">💡</span>
                            <h1 style="color: #ffffff; margin: 15px 0 5px 0; font-size: 32px; font-weight: 700;">Weekly Tips</h1>
                            <p style="color: #ffffff; margin: 0; font-size: 18px;">Master Topic</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <p style="font-size: 16px; color: #333; margin: 0 0 30px 0; line-height: 1.6;">
                                Hi {first_name},
                            </p>

                            <div style="background: #635bff; border-radius: 8px; padding: 25px; margin: 30px 0;">
                                <h2 style="margin: 0 0 15px 0; font-size: 22px; color: #92400e; font-weight: 700;">Tip #1: Start with the Basics</h2>
                                <p style="margin: 0; font-size: 15px; color: #78350f; line-height: 1.8;">
                                    Before diving into advanced techniques, make sure you have a solid foundation. Understanding the fundamentals will make everything else easier.
                                </p>
                            </div>

                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 30px 0;">
                                <h2 style="margin: 0 0 15px 0; font-size: 22px; color: #1a1a1a; font-weight: 700;">Tip #2: Practice Consistently</h2>
                                <p style="margin: 0; font-size: 15px; color: #666; line-height: 1.8;">
                                    Set aside 15 minutes daily for practice. Consistency beats intensity when building new skills.
                                </p>
                            </div>

                            <div style="background: #635bff; border-radius: 8px; padding: 25px; margin: 30px 0;">
                                <h2 style="margin: 0 0 15px 0; font-size: 22px; color: #92400e; font-weight: 700;">Tip #3: Learn from Others</h2>
                                <p style="margin: 0; font-size: 15px; color: #78350f; line-height: 1.8;">
                                    Join communities, follow experts, and don't be afraid to ask questions. Learning from others accelerates your progress.
                                </p>
                            </div>

                            <div style="border: 2px dashed #fbbf24; border-radius: 8px; padding: 25px; margin: 30px 0; text-align: center;">
                                <p style="margin: 0 0 15px 0; font-size: 16px; color: #92400e; font-weight: 600;">💪 Pro Tip of the Week</p>
                                <p style="margin: 0; font-size: 15px; color: #78350f; line-height: 1.8;">
                                    "Track your progress weekly. Small improvements compound into massive results over time!"
                                </p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #fbbf24; color: #78350f; padding: 16px 40px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 700;">Get More Tips</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center; border-radius: 0 0 12px 12px;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getWebinarInvitation(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="padding: 0;">
                            <img src="https://placehold.co/600x300/3b82f6/ffffff?text=Webinar+Banner" alt="Webinar" style="width: 100%; height: auto; display: block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <div style="text-align: center; margin-bottom: 30px;">
                                <span style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 8px 20px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Free Webinar</span>
                            </div>
                            <h1 style="color: #1a1a1a; margin: 0 0 20px 0; font-size: 32px; font-weight: 700; text-align: center; line-height: 1.3;">You're Invited: Mastering Success in 2025</h1>
                            <p style="font-size: 18px; color: #666; margin: 0 0 30px 0; text-align: center; line-height: 1.6;">
                                Join us for an exclusive online session packed with insights and actionable strategies.
                            </p>
                            <div style="background-color: #f0f9ff; border-radius: 8px; padding: 30px; margin: 30px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 10px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #0369a1; font-weight: 600;">📅 Date:</p>
                                            <p style="margin: 5px 0 0 0; font-size: 16px; color: #1e3a8a; font-weight: 700;">Thursday, December 15, 2025</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #0369a1; font-weight: 600;">⏰ Time:</p>
                                            <p style="margin: 5px 0 0 0; font-size: 16px; color: #1e3a8a; font-weight: 700;">2:00 PM EST (60 minutes)</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 0;">
                                            <p style="margin: 0; font-size: 14px; color: #0369a1; font-weight: 600;">👤 Speaker:</p>
                                            <p style="margin: 5px 0 0 0; font-size: 16px; color: #1e3a8a; font-weight: 700;">Jane Smith, Industry Expert</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <h3 style="margin: 30px 0 15px 0; font-size: 20px; color: #1a1a1a; font-weight: 700;">What You'll Learn:</h3>
                            <ul style="font-size: 16px; color: #666; line-height: 2; padding-left: 20px;">
                                <li>Key strategies for success in 2025</li>
                                <li>Real-world case studies and examples</li>
                                <li>Q&A with industry experts</li>
                                <li>Exclusive resources and templates</li>
                            </ul>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 18px 50px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);">Register Now - It's Free!</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size: 14px; color: #999; margin: 30px 0 0 0; text-align: center; line-height: 1.6;">
                                Spots are limited. Reserve yours today!
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getEventReminder(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #fef3c7;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef3c7; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="550" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; border: 3px solid #f59e0b;">
                    <tr>
                        <td style="padding: 40px; text-align: center;">
                            <div style="margin-bottom: 20px;">
                                <span style="font-size: 64px;">⏰</span>
                            </div>
                            <h1 style="color: #92400e; margin: 0 0 10px 0; font-size: 28px; font-weight: 700; text-transform: uppercase;">Reminder!</h1>
                            <p style="font-size: 18px; color: #78350f; margin: 0 0 30px 0; font-weight: 600;">Tomorrow: New Webinar on Best Practices</p>

                            <div style="background: #635bff; border-radius: 12px; padding: 30px; margin: 30px 0;">
                                <p style="color: #ffffff; margin: 0 0 15px 0; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Event Starts In</p>
                                <p style="color: #ffffff; margin: 0; font-size: 48px; font-weight: 900; line-height: 1;">24 HOURS</p>
                            </div>

                            <div style="background-color: #fffbeb; border-radius: 8px; padding: 25px; margin: 30px 0; text-align: left;">
                                <p style="margin: 0 0 15px 0; font-size: 16px; color: #92400e; font-weight: 700;">📍 Event Details:</p>
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 15px; color: #78350f;">
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>When:</strong></td>
                                        <td style="padding: 8px 0;">Tomorrow at 10:00 AM</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Where:</strong></td>
                                        <td style="padding: 8px 0;">Virtual Event Link</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Duration:</strong></td>
                                        <td style="padding: 8px 0;">90 minutes</td>
                                    </tr>
                                </table>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #f59e0b; color: #ffffff; padding: 18px 50px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 700; text-transform: uppercase;">Add to Calendar</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #92400e; margin: 30px 0 0 0; line-height: 1.6;">
                                Don't forget! We're looking forward to seeing you there.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getConferenceInvite(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #1a1a1a;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a1a; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden;">
                    <tr>
                        <td style="padding: 0; position: relative;">
                            <img src="https://placehold.co/600x350/667eea/ffffff?text=Conference+2025" alt="Conference" style="width: 100%; height: auto; display: block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 50px 40px;">
                            <h1 style="color: #1a1a1a; margin: 0 0 15px 0; font-size: 36px; font-weight: 700; line-height: 1.2;">Join Us at Conference 2025</h1>
                            <p style="font-size: 18px; color: #666; margin: 0 0 30px 0; line-height: 1.6;">
                                The industry's premier gathering of innovators, leaders, and visionaries.
                            </p>

                            <div style="background: #635bff; border-radius: 12px; padding: 35px; margin: 30px 0; text-align: center;">
                                <p style="color: #ffffff; margin: 0 0 10px 0; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px;">Save the Date</p>
                                <p style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 900;">March 15-17, 2025</p>
                                <p style="color: #ffffff; margin: 15px 0 0 0; font-size: 16px; opacity: 0.9;">San Francisco, CA</p>
                            </div>

                            <h3 style="margin: 35px 0 20px 0; font-size: 22px; color: #1a1a1a; font-weight: 700;">Conference Highlights:</h3>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                                <tr>
                                    <td style="padding: 15px 0; vertical-align: top; width: 40px;">
                                        <span style="font-size: 24px;">🎤</span>
                                    </td>
                                    <td style="padding: 15px 0; vertical-align: top;">
                                        <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">50+ Expert Speakers</h4>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Industry leaders sharing insights and strategies</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0; vertical-align: top;">
                                        <span style="font-size: 24px;">🤝</span>
                                    </td>
                                    <td style="padding: 15px 0; vertical-align: top;">
                                        <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">Networking Opportunities</h4>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Connect with 2,000+ professionals</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0; vertical-align: top;">
                                        <span style="font-size: 24px;">🎯</span>
                                    </td>
                                    <td style="padding: 15px 0; vertical-align: top;">
                                        <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">Hands-On Workshops</h4>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Learn practical skills from experts</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0; vertical-align: top;">
                                        <span style="font-size: 24px;">🎁</span>
                                    </td>
                                    <td style="padding: 15px 0; vertical-align: top;">
                                        <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1a1a1a; font-weight: 600;">Exclusive Swag & Resources</h4>
                                        <p style="margin: 0; font-size: 14px; color: #666; line-height: 1.6;">Take home valuable tools and materials</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0;">
                                <p style="margin: 0; font-size: 15px; color: #1e40af; line-height: 1.8;">
                                    <strong>Early Bird Special:</strong> Register by January 15 and save 30% on your ticket!
                                </p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 35px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background: #635bff; color: #ffffff; padding: 18px 50px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">Register Now</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getVirtualEvent(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #0f172a;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #0f172a; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #1e293b; border-radius: 12px; border: 2px solid #3b82f6;">
                    <tr>
                        <td style="padding: 50px 40px; text-align: center;">
                            <span style="font-size: 72px;">💻</span>
                            <h1 style="color: #ffffff; margin: 20px 0 10px 0; font-size: 36px; font-weight: 700;">Virtual Event</h1>
                            <h2 style="color: #3b82f6; margin: 0 0 30px 0; font-size: 28px; font-weight: 700;">Mastering Success in 2025</h2>

                            <div style="background: #635bff; border-radius: 12px; padding: 30px; margin: 30px 0;">
                                <p style="color: #ffffff; margin: 0 0 10px 0; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Join From Anywhere</p>
                                <p style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 600;">100% Online • Zero Travel Required</p>
                            </div>

                            <div style="background-color: #334155; border-radius: 8px; padding: 30px; margin: 30px 0; text-align: left;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 12px 0; color: #94a3b8; font-size: 15px;">
                                            📅 <strong style="color: #ffffff;">Date:</strong>
                                        </td>
                                        <td style="padding: 12px 0; text-align: right; color: #ffffff; font-size: 15px; font-weight: 600;">
                                            December 20, 2025
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; color: #94a3b8; font-size: 15px;">
                                            ⏰ <strong style="color: #ffffff;">Time:</strong>
                                        </td>
                                        <td style="padding: 12px 0; text-align: right; color: #ffffff; font-size: 15px; font-weight: 600;">
                                            3:00 PM EST
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; color: #94a3b8; font-size: 15px;">
                                            🌐 <strong style="color: #ffffff;">Platform:</strong>
                                        </td>
                                        <td style="padding: 12px 0; text-align: right; color: #ffffff; font-size: 15px; font-weight: 600;">
                                            Zoom
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="text-align: left; margin: 30px 0;">
                                <h3 style="color: #ffffff; margin: 0 0 15px 0; font-size: 20px; font-weight: 700;">What's Included:</h3>
                                <p style="color: #cbd5e1; margin: 0; font-size: 15px; line-height: 2;">
                                    ✓ Live interactive sessions<br>
                                    ✓ Q&A with speakers<br>
                                    ✓ Downloadable resources<br>
                                    ✓ Recording access for 30 days<br>
                                    ✓ Digital certificate of attendance
                                </p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 20px 60px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 700; text-transform: uppercase; box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);">Register Now</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #94a3b8; margin: 30px 0 0 0; font-size: 14px; line-height: 1.6;">
                                Limited spots available. Secure your spot today!
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 25px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getEventThankYou(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 50px 40px; text-align: center;">
                            <span style="font-size: 72px;">🎉</span>
                            <h1 style="color: #1a1a1a; margin: 20px 0 15px 0; font-size: 32px; font-weight: 700;">Thank You for Attending!</h1>
                            <p style="font-size: 18px; color: #666; margin: 0 0 30px 0; line-height: 1.6;">
                                We hope you enjoyed the event "Mastering Success in 2025". Your participation made it special!
                            </p>
                            <div style="background-color: #f0f9ff; border-radius: 12px; padding: 30px; margin: 30px 0;">
                                <h2 style="color: #1e40af; margin: 0 0 20px 0; font-size: 22px; font-weight: 700;">Event Resources</h2>
                                <p style="margin: 15px 0; font-size: 16px;"><a href="#" style="color: #3b82f6; text-decoration: none; font-weight: 600;">📊 Download Presentation Slides</a></p>
                                <p style="margin: 15px 0; font-size: 16px;"><a href="#" style="color: #3b82f6; text-decoration: none; font-weight: 600;">🎥 Watch Event Recording</a></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">© {year} {app_name}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getCartAbandonment(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px; text-align: center;">
                            <span style="font-size: 72px;">🛒</span>
                            <h1 style="color: #1a1a1a; margin: 20px 0 15px 0; font-size: 32px; font-weight: 700;">{first_name}, You Left Something Behind!</h1>
                            <p style="font-size: 18px; color: #666; margin: 0 0 30px 0; line-height: 1.6;">
                                The items in your cart are waiting for you. Complete your purchase before they're gone!
                            </p>

                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 30px; margin: 30px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="100" style="vertical-align: top;">
                                            <img src="https://placehold.co/100x100/e5e7eb/999999?text=Product" alt="Product" style="width: 100px; height: 100px; border-radius: 8px;">
                                        </td>
                                        <td style="padding-left: 20px; vertical-align: top; text-align: left;">
                                            <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">Product Name</h3>
                                            <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Size: Medium, Color: Blue</p>
                                            <p style="margin: 0; font-size: 20px; color: #1a1a1a; font-weight: 700;">$49.99</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="background: #635bff; border-radius: 12px; padding: 25px; margin: 30px 0;">
                                <p style="color: #ffffff; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">🎁 SPECIAL OFFER</p>
                                <p style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">Complete Your Order & Get 10% Off!</p>
                                <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Use code: <strong>COMEBACK10</strong></p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 18px 50px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);">Complete My Purchase</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="border-top: 1px solid #e5e7eb; padding-top: 30px; margin-top: 30px;">
                                <p style="font-size: 14px; color: #666; margin: 0; line-height: 1.6;">
                                    <strong>Need help?</strong> Our customer service team is here for you 24/7.<br>
                                    <a href="#" style="color: #3b82f6; text-decoration: none;">Contact Support</a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getProductRecommendation(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px;">
                            <h1 style="color: #1a1a1a; margin: 0 0 15px 0; font-size: 32px; font-weight: 700;">Picked Just For You, {first_name}</h1>
                            <p style="font-size: 16px; color: #666; margin: 0 0 30px 0; line-height: 1.6;">
                                Based on your interests, we think you'll love these products.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td width="48%" style="vertical-align: top; padding-right: 2%;">
                                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                                            <img src="https://placehold.co/280x280/667eea/ffffff?text=Product+1" alt="Product" style="width: 100%; height: auto; display: block;">
                                            <div style="padding: 20px;">
                                                <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">Premium Headphones</h3>
                                                <p style="margin: 0 0 15px 0; font-size: 14px; color: #666; line-height: 1.6;">Wireless, noise-cancelling, 30hr battery</p>
                                                <p style="margin: 0 0 15px 0; font-size: 22px; color: #1a1a1a; font-weight: 700;">$199.99</p>
                                                <a href="#" style="display: block; background-color: #3b82f6; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; text-align: center;">View Product</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td width="48%" style="vertical-align: top; padding-left: 2%;">
                                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                                            <img src="https://placehold.co/280x280/764ba2/ffffff?text=Product+2" alt="Product" style="width: 100%; height: auto; display: block;">
                                            <div style="padding: 20px;">
                                                <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">Smart Watch Pro</h3>
                                                <p style="margin: 0 0 15px 0; font-size: 14px; color: #666; line-height: 1.6;">Fitness tracking, GPS, waterproof</p>
                                                <p style="margin: 0 0 15px 0; font-size: 22px; color: #1a1a1a; font-weight: 700;">$349.99</p>
                                                <a href="#" style="display: block; background-color: #3b82f6; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; text-align: center;">View Product</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td width="48%" style="vertical-align: top; padding-right: 2%;">
                                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                                            <img src="https://placehold.co/280x280/10b981/ffffff?text=Product+3" alt="Product" style="width: 100%; height: auto; display: block;">
                                            <div style="padding: 20px;">
                                                <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">Laptop Stand</h3>
                                                <p style="margin: 0 0 15px 0; font-size: 14px; color: #666; line-height: 1.6;">Ergonomic, adjustable, aluminum</p>
                                                <p style="margin: 0 0 15px 0; font-size: 22px; color: #1a1a1a; font-weight: 700;">$79.99</p>
                                                <a href="#" style="display: block; background-color: #3b82f6; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; text-align: center;">View Product</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td width="48%" style="vertical-align: top; padding-left: 2%;">
                                        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                                            <img src="https://placehold.co/280x280/f59e0b/ffffff?text=Product+4" alt="Product" style="width: 100%; height: auto; display: block;">
                                            <div style="padding: 20px;">
                                                <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">Wireless Charger</h3>
                                                <p style="margin: 0 0 15px 0; font-size: 14px; color: #666; line-height: 1.6;">Fast charging, sleek design</p>
                                                <p style="margin: 0 0 15px 0; font-size: 22px; color: #1a1a1a; font-weight: 700;">$39.99</p>
                                                <a href="#" style="display: block; background-color: #3b82f6; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; text-align: center;">View Product</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="text-align: center; margin: 40px 0 20px 0;">
                                <a href="#" style="color: #3b82f6; text-decoration: none; font-size: 16px; font-weight: 600;">Browse All Products →</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getBackInStock(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #ecfdf5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #ecfdf5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; border: 2px solid #10b981;">
                    <tr>
                        <td style="padding: 40px; text-align: center;">
                            <div style="margin-bottom: 20px;">
                                <span style="display: inline-block; background-color: #10b981; color: #ffffff; padding: 10px 25px; border-radius: 25px; font-size: 14px; font-weight: 700; text-transform: uppercase;">Back in Stock</span>
                            </div>
                            <h1 style="color: #1a1a1a; margin: 20px 0 15px 0; font-size: 32px; font-weight: 700;">Mastering Success in 2025 is Back!</h1>
                            <p style="font-size: 18px; color: #666; margin: 0 0 30px 0; line-height: 1.6;">
                                Great news! The item you've been waiting for is now available.
                            </p>

                            <div style="margin: 30px 0;">
                                <img src="https://placehold.co/400x400/10b981/ffffff?text=Product+Image" alt="Product" style="max-width: 400px; width: 100%; height: auto; border-radius: 8px;">
                            </div>

                            <div style="background-color: #f0fdf4; border-radius: 8px; padding: 25px; margin: 30px 0; text-align: left;">
                                <h2 style="margin: 0 0 15px 0; font-size: 24px; color: #1a1a1a; font-weight: 700;">Mastering Success in 2025</h2>
                                <p style="margin: 0 0 15px 0; font-size: 15px; color: #666; line-height: 1.8;">
                                    This popular item sold out quickly, but it's back and ready to ship. Don't miss out this time!
                                </p>
                                <div style="display: flex; align-items: center; margin-top: 20px;">
                                    <p style="margin: 0; font-size: 32px; color: #1a1a1a; font-weight: 700;">$149.99</p>
                                </div>
                            </div>

                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 30px 0; text-align: left;">
                                <p style="margin: 0; font-size: 15px; color: #92400e; line-height: 1.8;">
                                    ⚡ <strong>Limited Stock Available</strong><br>
                                    This item is in high demand. Order now to avoid missing out again!
                                </p>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #10b981; color: #ffffff; padding: 18px 50px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 700; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">Shop Now</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #666; margin: 30px 0 0 0; line-height: 1.6;">
                                🚚 Free shipping on orders over $50<br>
                                ✓ 30-day money-back guarantee
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getReviewRequest(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px; text-align: center;">
                            <span style="font-size: 64px;">⭐</span>
                            <h1 style="color: #1a1a1a; margin: 20px 0 15px 0; font-size: 28px; font-weight: 700;">How Was Your Purchase, {first_name}?</h1>
                            <p style="font-size: 16px; color: #666; margin: 0 0 30px 0; line-height: 1.6;">
                                We'd love to hear about your experience with your recent order!
                            </p>

                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 30px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="100" style="vertical-align: middle;">
                                            <img src="https://placehold.co/100x100/e5e7eb/999999?text=Product" alt="Product" style="width: 100px; height: 100px; border-radius: 8px;">
                                        </td>
                                        <td style="padding-left: 20px; vertical-align: middle; text-align: left;">
                                            <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">Product Name</h3>
                                            <p style="margin: 0; font-size: 14px; color: #666;">Purchased on Nov 1, 2025</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <h2 style="color: #1a1a1a; margin: 30px 0 20px 0; font-size: 22px; font-weight: 700;">Rate Your Experience</h2>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; margin: 0 5px; text-decoration: none; font-size: 40px;">⭐</a>
                                        <a href="#" style="display: inline-block; margin: 0 5px; text-decoration: none; font-size: 40px;">⭐</a>
                                        <a href="#" style="display: inline-block; margin: 0 5px; text-decoration: none; font-size: 40px;">⭐</a>
                                        <a href="#" style="display: inline-block; margin: 0 5px; text-decoration: none; font-size: 40px;">⭐</a>
                                        <a href="#" style="display: inline-block; margin: 0 5px; text-decoration: none; font-size: 40px;">⭐</a>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 16px 50px; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 700;">Write a Review</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #fef3c7; border-radius: 8px; padding: 20px; margin: 30px 0;">
                                <p style="margin: 0; font-size: 15px; color: #92400e; line-height: 1.8;">
                                    <strong>🎁 Get 10% Off Your Next Order</strong><br>
                                    Leave a review and we'll send you a discount code!
                                </p>
                            </div>

                            <p style="font-size: 14px; color: #999; margin: 30px 0 0 0; line-height: 1.6;">
                                Your feedback helps other customers make informed decisions<br>
                                and helps us improve our products and service.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {year} {app_name}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getBirthdayDiscount(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #635bff;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px;">
                    <tr>
                        <td style="padding: 50px 40px; text-align: center;">
                            <span style="font-size: 80px;">🎂</span>
                            <h1 style="color: #1a1a1a; margin: 20px 0 10px 0; font-size: 36px; font-weight: 700;">Happy Birthday {first_name}!</h1>
                            <p style="font-size: 20px; color: #666; margin: 0 0 30px 0;">
                                Wishing you an amazing day filled with joy!
                            </p>
                            <div style="background: #635bff; border-radius: 16px; padding: 40px; margin: 30px 0;">
                                <p style="color: #ffffff; margin: 0 0 15px 0; font-size: 18px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px;">Your Birthday Gift</p>
                                <p style="color: #ffffff; margin: 0; font-size: 64px; font-weight: 900; line-height: 1;">25% OFF</p>
                                <p style="color: #ffffff; margin: 15px 0 0 0; font-size: 16px;">On your entire purchase!</p>
                            </div>
                            <div style="background-color: #fffbeb; border: 2px dashed #f59e0b; border-radius: 8px; padding: 25px; margin: 30px 0;">
                                <p style="margin: 0 0 10px 0; font-size: 14px; color: #92400e; font-weight: 600; text-transform: uppercase;">Your Birthday Code</p>
                                <p style="margin: 0; font-size: 32px; color: #f59e0b; font-weight: 900; letter-spacing: 3px;">BDAY25</p>
                                <p style="margin: 15px 0 0 0; font-size: 13px; color: #92400e;">Valid for 7 days</p>
                            </div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="#" style="display: inline-block; background-color: #667eea; color: #ffffff; padding: 18px 50px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 700;">Celebrate with Shopping</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">© {year} {app_name}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

}
