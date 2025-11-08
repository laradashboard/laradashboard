<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmailTemplate;
use App\Enums\TemplateType;
use Illuminate\Support\Str;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        $templates = [
            [
                'uuid' => Str::uuid(),
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{company}} - {{first_name}}!',
                'body_html' => $this->getWelcomeHtmlTemplate(),
                'body_text' => $this->getWelcomeTextTemplate(),
                'type' => TemplateType::WELCOME,
                'description' => 'Welcome email template for new contacts',
                'variables' => ['first_name', 'last_name', 'company', 'email'],
                'is_active' => true,
                'is_default' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Follow Up Email',
                'subject' => 'Following up on our conversation - {{first_name}}',
                'body_html' => $this->getFollowUpHtmlTemplate(),
                'body_text' => $this->getFollowUpTextTemplate(),
                'type' => TemplateType::FOLLOW_UP,
                'description' => 'Follow up email template for contact engagement',
                'variables' => ['first_name', 'last_name', 'company', 'job_title'],
                'is_active' => true,
                'is_default' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Monthly Newsletter',
                'subject' => 'Your Monthly Update from {{company}}',
                'body_html' => $this->getNewsletterHtmlTemplate(),
                'body_text' => $this->getNewsletterTextTemplate(),
                'type' => TemplateType::NEWSLETTER,
                'description' => 'Monthly newsletter template',
                'variables' => ['first_name', 'company'],
                'is_active' => true,
                'is_default' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Promotional Offer',
                'subject' => 'Special Offer Just for You, {{first_name}}!',
                'body_html' => $this->getPromotionalHtmlTemplate(),
                'body_text' => $this->getPromotionalTextTemplate(),
                'type' => TemplateType::PROMOTIONAL,
                'description' => 'Promotional email template for special offers',
                'variables' => ['first_name', 'company'],
                'is_active' => true,
                'is_default' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Meeting Reminder',
                'subject' => 'Reminder: Meeting Tomorrow - {{first_name}}',
                'body_html' => $this->getReminderHtmlTemplate(),
                'body_text' => $this->getReminderTextTemplate(),
                'type' => TemplateType::REMINDER,
                'description' => 'Meeting reminder template',
                'variables' => ['first_name', 'meeting_date', 'meeting_time'],
                'is_active' => true,
                'is_default' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Activity Email',
                'subject' => 'Activity Notification - {{activity_title}}',
                'body_html' => $this->getActivityHtmlTemplate(),
                'body_text' => $this->getActivityTextTemplate(),
                'type' => TemplateType::EMAIL,
                'description' => 'Activity management email template',
                'variables' => ['first_name', 'activity_title', 'activity_description', 'due_date'],
                'is_active' => true,
                'is_default' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }

        Model::reguard();
    }

    private function getWelcomeHtmlTemplate(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #007bff;">Welcome to {{company}}, {{first_name}}!</h1>
                
                <p>Hi {{first_name}},</p>
                
                <p>Welcome to {{company}}! We\'re thrilled to have you join our community.</p>
                
                <p>Here\'s what you can expect from us:</p>
                <ul>
                    <li>Personalized service tailored to your needs</li>
                    <li>Regular updates on new products and services</li>
                    <li>Exclusive offers and promotions</li>
                    <li>24/7 customer support</li>
                </ul>
                
                <p>If you have any questions or need assistance, don\'t hesitate to reach out to us.</p>
                
                <p>Best regards,<br>
                The {{company}} Team</p>
                
                <hr style="margin: 30px 0;">
                <p style="font-size: 12px; color: #666;">
                    You received this email because you signed up for {{company}}.
                </p>
            </div>
        </body>
        </html>';
    }

    private function getWelcomeTextTemplate(): string
    {
        return 'Welcome to {{company}}, {{first_name}}!

Hi {{first_name}},

Welcome to {{company}}! We\'re thrilled to have you join our community.

Here\'s what you can expect from us:
- Personalized service tailored to your needs
- Regular updates on new products and services
- Exclusive offers and promotions
- 24/7 customer support

If you have any questions or need assistance, don\'t hesitate to reach out to us.

Best regards,
The {{company}} Team

---
You received this email because you signed up for {{company}}.';
    }

    private function getFollowUpHtmlTemplate(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Follow Up</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #007bff;">Following up on our conversation</h1>
                
                <p>Hi {{first_name}},</p>
                
                <p>I hope this email finds you well. I wanted to follow up on our recent conversation about your needs at {{company}}.</p>
                
                <p>As a {{job_title}}, I understand you have unique challenges and requirements. I\'d love to discuss how we can help you achieve your goals.</p>
                
                <p>Would you be available for a quick call this week to explore potential solutions?</p>
                
                <p>Looking forward to hearing from you.</p>
                
                <p>Best regards,<br>
                Your Account Manager</p>
                
                <hr style="margin: 30px 0;">
                <p style="font-size: 12px; color: #666;">
                    This is a follow-up email regarding your inquiry.
                </p>
            </div>
        </body>
        </html>';
    }

    private function getFollowUpTextTemplate(): string
    {
        return 'Following up on our conversation

Hi {{first_name}},

I hope this email finds you well. I wanted to follow up on our recent conversation about your needs at {{company}}.

As a {{job_title}}, I understand you have unique challenges and requirements. I\'d love to discuss how we can help you achieve your goals.

Would you be available for a quick call this week to explore potential solutions?

Looking forward to hearing from you.

Best regards,
Your Account Manager

---
This is a follow-up email regarding your inquiry.';
    }

    private function getNewsletterHtmlTemplate(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Newsletter</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #007bff;">Your Monthly Update from {{company}}</h1>
                
                <p>Hi {{first_name}},</p>
                
                <p>Here\'s your monthly roundup of news, updates, and insights from {{company}}.</p>
                
                <h2>This Month\'s Highlights</h2>
                <ul>
                    <li>New product features and improvements</li>
                    <li>Customer success stories</li>
                    <li>Industry insights and trends</li>
                    <li>Upcoming events and webinars</li>
                </ul>
                
                <h2>Featured Content</h2>
                <p>Check out our latest blog posts and resources to help you stay ahead in your industry.</p>
                
                <p>Thank you for being a valued member of our community!</p>
                
                <p>Best regards,<br>
                The {{company}} Team</p>
                
                <hr style="margin: 30px 0;">
                <p style="font-size: 12px; color: #666;">
                    You\'re receiving this newsletter because you subscribed to updates from {{company}}.
                </p>
            </div>
        </body>
        </html>';
    }

    private function getNewsletterTextTemplate(): string
    {
        return 'Your Monthly Update from {{company}}

Hi {{first_name}},

Here\'s your monthly roundup of news, updates, and insights from {{company}}.

This Month\'s Highlights:
- New product features and improvements
- Customer success stories
- Industry insights and trends
- Upcoming events and webinars

Featured Content:
Check out our latest blog posts and resources to help you stay ahead in your industry.

Thank you for being a valued member of our community!

Best regards,
The {{company}} Team

---
You\'re receiving this newsletter because you subscribed to updates from {{company}}.';
    }

    private function getPromotionalHtmlTemplate(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Special Offer</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #007bff;">Special Offer Just for You!</h1>
                
                <p>Hi {{first_name}},</p>
                
                <p>We have an exclusive offer that we think you\'ll love!</p>
                
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0;">
                    <h2 style="color: #dc3545; margin: 0;">LIMITED TIME OFFER</h2>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">Save 25% on All Products!</p>
                    <p style="margin: 0;">Use code: SAVE25</p>
                </div>
                
                <p>This exclusive discount is our way of saying thank you for being a valued customer.</p>
                
                <p>Hurry! This offer expires in 7 days.</p>
                
                <p style="text-align: center;">
                    <a href="#" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Shop Now</a>
                </p>
                
                <p>Best regards,<br>
                The {{company}} Team</p>
                
                <hr style="margin: 30px 0;">
                <p style="font-size: 12px; color: #666;">
                    This promotional email was sent to valued {{company}} customers.
                </p>
            </div>
        </body>
        </html>';
    }

    private function getPromotionalTextTemplate(): string
    {
        return 'Special Offer Just for You!

Hi {{first_name}},

We have an exclusive offer that we think you\'ll love!

LIMITED TIME OFFER
Save 25% on All Products!
Use code: SAVE25

This exclusive discount is our way of saying thank you for being a valued customer.

Hurry! This offer expires in 7 days.

Visit our website to shop now and apply your discount code at checkout.

Best regards,
The {{company}} Team

---
This promotional email was sent to valued {{company}} customers.';
    }

    private function getReminderHtmlTemplate(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Meeting Reminder</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #007bff;">Meeting Reminder</h1>
                
                <p>Hi {{first_name}},</p>
                
                <p>This is a friendly reminder about our upcoming meeting scheduled for tomorrow.</p>
                
                <div style="background-color: #e9f4ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3 style="margin: 0 0 10px 0;">Meeting Details:</h3>
                    <p style="margin: 5px 0;"><strong>Date:</strong> {{meeting_date}}</p>
                    <p style="margin: 5px 0;"><strong>Time:</strong> {{meeting_time}}</p>
                    <p style="margin: 5px 0;"><strong>Duration:</strong> 30 minutes</p>
                </div>
                
                <p>Please let me know if you need to reschedule or if you have any questions before our meeting.</p>
                
                <p>Looking forward to speaking with you!</p>
                
                <p>Best regards,<br>
                Your Account Manager</p>
                
                <hr style="margin: 30px 0;">
                <p style="font-size: 12px; color: #666;">
                    This is an automated meeting reminder.
                </p>
            </div>
        </body>
        </html>';
    }

    private function getReminderTextTemplate(): string
    {
        return 'Meeting Reminder

Hi {{first_name}},

This is a friendly reminder about our upcoming meeting scheduled for tomorrow.

Meeting Details:
Date: {{meeting_date}}
Time: {{meeting_time}}
Duration: 30 minutes

Please let me know if you need to reschedule or if you have any questions before our meeting.

Looking forward to speaking with you!

Best regards,
Your Account Manager

---
This is an automated meeting reminder.';
    }

    private function getActivityHtmlTemplate(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Activity Notification</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #007bff;">Activity Notification</h1>
                
                <p>Hi {{first_name}},</p>
                
                <p>You have an activity that requires your attention.</p>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3 style="margin: 0 0 10px 0;">{{activity_title}}</h3>
                    <p style="margin: 5px 0;">{{activity_description}}</p>
                    <p style="margin: 5px 0;"><strong>Due Date:</strong> {{due_date}}</p>
                </div>
                
                <p>Please make sure to complete this activity on time.</p>
                
                <p>Best regards,<br>
                Your Team</p>
                
                <hr style="margin: 30px 0;">
                <p style="font-size: 12px; color: #666;">
                    This is an automated activity notification.
                </p>
            </div>
        </body>
        </html>';
    }

    private function getActivityTextTemplate(): string
    {
        return 'Activity Notification

Hi {{first_name}},

You have an activity that requires your attention.

Activity: {{activity_title}}
Description: {{activity_description}}
Due Date: {{due_date}}

Please make sure to complete this activity on time.

Best regards,
Your Team

---
This is an automated activity notification.';
    }
}
