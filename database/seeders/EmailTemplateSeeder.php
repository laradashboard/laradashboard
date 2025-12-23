<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TemplateType;
use App\Models\EmailTemplate;
use App\Services\Builder\BlockService;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function __construct(private readonly BlockService $blockService)
    {
    }

    public function run(): void
    {
        $templates = $this->getAllTemplates();
        $created = 0;
        $skipped = 0;

        foreach ($templates as $template) {
            $existing = EmailTemplate::where('name', $template['name'])->first();

            if ($existing) {
                if ($existing->is_deleteable) {
                    $existing->update($template);
                    $created++;
                } else {
                    $skipped++; // Essential template from migration
                }
            } else {
                EmailTemplate::create($template);
                $created++;
            }
        }

        $this->command->info("✅ Created/updated {$created} email template(s).");
        if ($skipped > 0) {
            $this->command->info("→ Skipped {$skipped} essential template(s) (already exist from migration).");
        }
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
            $this->blockService->createTemplateData(
                'Forgot Password',
                'Reset Your Password - {app_name}',
                TemplateType::AUTHENTICATION->value,
                'Password reset email with security tips',
                $this->getPasswordResetBlocks(),
                true,
                false
            ),
        ];
    }

    private function getWelcomeTemplates(): array
    {
        return [
            $this->blockService->createTemplateData(
                'Modern Welcome - Blue',
                'Welcome to {app_name}, {first_name}! 🎉',
                TemplateType::WELCOME->value,
                'Modern blue-themed welcome email',
                $this->getModernWelcomeBlueBlocks()
            ),
            $this->blockService->createTemplateData(
                'Welcome with Video',
                'Hi {first_name}, Watch Our Welcome Video!',
                TemplateType::WELCOME->value,
                'Welcome email with embedded video',
                $this->getWelcomeWithVideoBlocks()
            ),
            $this->blockService->createTemplateData(
                'Minimalist Welcome',
                'Welcome {first_name} - Let\'s Get Started',
                TemplateType::WELCOME->value,
                'Clean minimalist welcome design',
                $this->getMinimalistWelcomeBlocks()
            ),
            $this->blockService->createTemplateData(
                'Welcome with Checklist',
                'Your Getting Started Guide, {first_name}',
                TemplateType::WELCOME->value,
                'Welcome with actionable checklist',
                $this->getWelcomeChecklistBlocks()
            ),
            $this->blockService->createTemplateData(
                'Bold Welcome',
                '{first_name}, You\'re In! Welcome Aboard 🚀',
                TemplateType::WELCOME->value,
                'Bold and energetic welcome email',
                $this->getBoldWelcomeBlocks()
            ),
        ];
    }

    private function getMarketingTemplates(): array
    {
        return [
            $this->blockService->createTemplateData(
                'Flash Sale - Urgent',
                '⚡ Flash Sale! {first_name}, 24 Hours Only',
                TemplateType::PROMOTIONAL->value,
                'Urgent flash sale template',
                $this->getFlashSaleBlocks()
            ),
            $this->blockService->createTemplateData(
                'Product Launch',
                'Introducing Our Latest: Mastering Success in 2025',
                TemplateType::PROMOTIONAL->value,
                'Product launch announcement',
                $this->getProductLaunchBlocks()
            ),
            $this->blockService->createTemplateData(
                'Limited Offer',
                '{first_name}, Exclusive Offer Expires Soon',
                TemplateType::PROMOTIONAL->value,
                'Limited time offer template',
                $this->getLimitedOfferBlocks()
            ),
            $this->blockService->createTemplateData(
                'Black Friday Special',
                'BLACK FRIDAY: Up to 70% Off!',
                TemplateType::PROMOTIONAL->value,
                'Black Friday sale template',
                $this->getBlackFridayBlocks()
            ),
            $this->blockService->createTemplateData(
                'Cyber Monday',
                'Cyber Monday Deals Start NOW!',
                TemplateType::PROMOTIONAL->value,
                'Cyber Monday template',
                $this->getCyberMondayBlocks()
            ),
        ];
    }

    private function getTransactionalTemplates(): array
    {
        return [
            $this->blockService->createTemplateData(
                'Order Confirmation',
                'Order Confirmed - #1010123',
                TemplateType::TRANSACTIONAL->value,
                'Order confirmation email',
                $this->getOrderConfirmationBlocks()
            ),
            $this->blockService->createTemplateData(
                'Shipping Notification',
                'Your Order Has Shipped! 📦',
                TemplateType::TRANSACTIONAL->value,
                'Shipping notification',
                $this->getShippingNotificationBlocks()
            ),
            $this->blockService->createTemplateData(
                'Invoice',
                'Invoice #1010123 from {app_name}',
                TemplateType::TRANSACTIONAL->value,
                'Invoice template',
                $this->getInvoiceBlocks()
            ),
            $this->blockService->createTemplateData(
                'Receipt',
                'Your Receipt from {app_name}',
                TemplateType::TRANSACTIONAL->value,
                'Payment receipt',
                $this->getReceiptBlocks()
            ),
        ];
    }

    private function getNewsletterTemplates(): array
    {
        return [
            $this->blockService->createTemplateData(
                'Weekly Digest',
                '📰 Your Weekly Update - {date}',
                TemplateType::NEWSLETTER->value,
                'Weekly newsletter digest',
                $this->getWeeklyDigestBlocks()
            ),
            $this->blockService->createTemplateData(
                'Blog Roundup',
                'Top Articles This Month',
                TemplateType::NEWSLETTER->value,
                'Blog posts roundup',
                $this->getBlogRoundupBlocks()
            ),
            $this->blockService->createTemplateData(
                'Industry News',
                'Industry Insights for Industry 2025',
                TemplateType::NEWSLETTER->value,
                'Industry news newsletter',
                $this->getIndustryNewsBlocks()
            ),
            $this->blockService->createTemplateData(
                'Company Newsletter',
                'Company News & Updates - {month}',
                TemplateType::NEWSLETTER->value,
                'Company newsletter',
                $this->getCompanyNewsletterBlocks()
            ),
            $this->blockService->createTemplateData(
                'Tips & Tricks',
                'Weekly Tips: Boost Your Productivity',
                TemplateType::NEWSLETTER->value,
                'Tips and tricks newsletter',
                $this->getTipsNewsletterBlocks()
            ),
        ];
    }

    private function getEventTemplates(): array
    {
        return [
            $this->blockService->createTemplateData(
                'Webinar Invitation',
                'You\'re Invited: Mastering Success in {year}',
                TemplateType::REMINDER->value,
                'Webinar invitation',
                $this->getWebinarInvitationBlocks()
            ),
            $this->blockService->createTemplateData(
                'Event Reminder',
                'Tomorrow: Mastering Success in {year} Starts at {time}',
                TemplateType::REMINDER->value,
                'Event reminder',
                $this->getEventReminderBlocks()
            ),
            $this->blockService->createTemplateData(
                'Conference Invite',
                'Join Us at the Annual Conference {year}',
                TemplateType::REMINDER->value,
                'Conference invitation',
                $this->getConferenceInviteBlocks()
            ),
            $this->blockService->createTemplateData(
                'Virtual Event',
                'Virtual Event: Mastering Success in {year} - Register Now',
                TemplateType::REMINDER->value,
                'Virtual event template',
                $this->getVirtualEventBlocks()
            ),
            $this->blockService->createTemplateData(
                'Event Thank You',
                'Thank You for Attending Mastering Success in {year}',
                TemplateType::FOLLOW_UP->value,
                'Post-event thank you',
                $this->getEventThankYouBlocks()
            ),
        ];
    }

    private function getEcommerceTemplates(): array
    {
        return [
            $this->blockService->createTemplateData(
                'Cart Abandonment',
                '{first_name}, You Left Something Behind! 🛒',
                TemplateType::REMINDER->value,
                'Abandoned cart recovery',
                $this->getCartAbandonmentBlocks()
            ),
            $this->blockService->createTemplateData(
                'Product Recommendation',
                'Based on Your Interests, {first_name}',
                TemplateType::PROMOTIONAL->value,
                'Personalized recommendations',
                $this->getProductRecommendationBlocks()
            ),
            $this->blockService->createTemplateData(
                'Back in Stock',
                'Product XYZ is Back in Stock!',
                TemplateType::PROMOTIONAL->value,
                'Back in stock notification',
                $this->getBackInStockBlocks()
            ),
            $this->blockService->createTemplateData(
                'Review Request',
                'How Was Your Purchase, {first_name}?',
                TemplateType::FOLLOW_UP->value,
                'Product review request',
                $this->getReviewRequestBlocks()
            ),
            $this->blockService->createTemplateData(
                'Birthday Discount',
                'Happy Birthday {first_name}! 🎂',
                TemplateType::PROMOTIONAL->value,
                'Birthday special offer',
                $this->getBirthdayDiscountBlocks()
            ),
        ];
    }

    // ========================================
    // TEMPLATE BLOCKS - Auth Templates
    // ========================================

    private function getPasswordResetBlocks(): array
    {
        return [
            $this->blockService->text('{site_icon_image}', 'center'),
            $this->blockService->spacer('10px'),
            $this->blockService->heading('Password Reset Request', 'h1', 'center', '#333333', '28px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Hello <strong>{full_name}</strong>,'),
            $this->blockService->spacer('10px'),
            $this->blockService->text('We received a request to reset your password for your <strong>{app_name}</strong> account. If you didn\'t make this request, you can safely ignore this email.'),
            $this->blockService->spacer('10px'),
            $this->blockService->text('To reset your password, click the button below:'),
            $this->blockService->spacer('20px'),
            $this->blockService->button('Reset My Password', '{reset_url}', '#635bff'),
            $this->blockService->spacer('20px'),
            $this->blockService->quote('⚠️ Important: This password reset link will expire in {expiry_time}. If the link expires, you\'ll need to request a new password reset.'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('If the button above doesn\'t work, copy and paste this URL into your browser:', 'left', '#666666', '14px'),
            $this->blockService->text('{reset_url}', 'left', '#635bff', '13px'),
            $this->blockService->spacer('20px'),
            $this->blockService->divider(),
            $this->blockService->text('<strong>Security Tips:</strong>', 'left', '#333333'),
            $this->blockService->listBlock([
                'Never share your password with anyone',
                'Use a strong, unique password',
                'Enable two-factor authentication if available',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    // ========================================
    // TEMPLATE BLOCKS - Welcome Templates
    // ========================================

    private function getModernWelcomeBlueBlocks(): array
    {
        return [
            $this->blockService->heading('Welcome to {app_name}! 🎉', 'h1', 'center', '#635bff', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Hi <strong>{first_name}</strong>,', 'center'),
            $this->blockService->spacer('10px'),
            $this->blockService->text('We\'re thrilled to have you on board! You\'ve just joined a community of thousands of users who are transforming the way they work.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('What\'s Next?', 'h2', 'center', '#333333', '24px'),
            $this->blockService->spacer('15px'),
            $this->blockService->listBlock([
                'Complete your profile setup',
                'Explore our features and tools',
                'Connect with other members',
                'Check out our help center',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Get Started Now', '{dashboard_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('Need help? Our support team is always here for you.', 'center', '#888888', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->divider(),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getWelcomeWithVideoBlocks(): array
    {
        return [
            $this->blockService->heading('Welcome, {first_name}! 👋', 'h1', 'center', '#333333', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('We\'ve prepared a quick video to help you get started with {app_name}. Watch it to discover all the amazing features waiting for you!', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->video('https://placehold.co/600x340/635bff/ffffff?text=Welcome+Video', 'https://www.youtube.com/watch?v=example', 'Welcome Video'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Ready to dive in?', 'h2', 'center', '#333333', '24px'),
            $this->blockService->text('Click the button below to access your dashboard and start exploring.', 'center'),
            $this->blockService->spacer('20px'),
            $this->blockService->button('Go to Dashboard', '{dashboard_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getMinimalistWelcomeBlocks(): array
    {
        return [
            $this->blockService->spacer('40px'),
            $this->blockService->heading('Welcome', 'h1', 'center', '#333333', '36px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('{first_name}, your account is ready.', 'center', '#666666', '18px'),
            $this->blockService->spacer('40px'),
            $this->blockService->button('Start Using {app_name}', '{dashboard_url}', '#333333'),
            $this->blockService->spacer('40px'),
            $this->blockService->divider('#e5e7eb', '1px', '60%'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Questions? Reply to this email.', 'center', '#999999', '14px'),
            $this->blockService->spacer('40px'),
        ];
    }

    private function getWelcomeChecklistBlocks(): array
    {
        return [
            $this->blockService->heading('Your Getting Started Guide', 'h1', 'center', '#333333', '28px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Hi {first_name}! Here\'s your personalized checklist to make the most of {app_name}:', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->listBlock([
                '✅ Create your account - Done!',
                '⬜ Complete your profile',
                '⬜ Set up your preferences',
                '⬜ Invite team members',
                '⬜ Create your first project',
            ], 'none'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Complete Your Profile', '{profile_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->quote('Pro tip: Users who complete their profile are 3x more likely to achieve their goals with {app_name}!'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getBoldWelcomeBlocks(): array
    {
        return [
            $this->blockService->heading('🚀 YOU\'RE IN!', 'h1', 'center', '#635bff', '40px'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('Welcome Aboard, {first_name}!', 'h2', 'center', '#333333', '24px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Get ready for an amazing journey with {app_name}. We\'re excited to have you as part of our community!', 'center', '#666666', '18px'),
            $this->blockService->spacer('40px'),
            $this->blockService->button('LET\'S GO! 🎯', '{dashboard_url}', '#635bff'),
            $this->blockService->spacer('40px'),
            $this->blockService->social(['twitter' => '#', 'linkedin' => '#', 'instagram' => '#']),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    // ========================================
    // TEMPLATE BLOCKS - Marketing Templates
    // ========================================

    private function getFlashSaleBlocks(): array
    {
        return [
            $this->blockService->heading('⚡ FLASH SALE ⚡', 'h1', 'center', '#ff4757', '36px'),
            $this->blockService->spacer('20px'),
            $this->blockService->countdown('Offer Ends In'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Up to 50% OFF Everything!', 'h2', 'center', '#333333', '28px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Hey {first_name}, this is your chance to grab amazing deals. Don\'t wait - these prices won\'t last!', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('SHOP NOW →', '{shop_url}', '#ff4757'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Use code: <strong>FLASH50</strong> at checkout', 'center', '#666666', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getProductLaunchBlocks(): array
    {
        return [
            $this->blockService->heading('Introducing Something Amazing', 'h1', 'center', '#333333', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->image('https://placehold.co/600x300/635bff/ffffff?text=New+Product', 'New Product Launch'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Mastering Success in 2025', 'h2', 'center', '#635bff', '24px'),
            $this->blockService->spacer('15px'),
            $this->blockService->text('We\'ve been working hard on something special, and today we\'re excited to share it with you. This is not just another product – it\'s a game-changer.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->listBlock([
                'Revolutionary features you\'ve never seen before',
                'Designed with your feedback in mind',
                'Available at an exclusive launch price',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Learn More', '{product_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getLimitedOfferBlocks(): array
    {
        return [
            $this->blockService->heading('🔥 Exclusive Offer for You', 'h1', 'center', '#333333', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('{first_name}, as a valued member, you\'re getting early access to our best deal of the year.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('40% OFF', 'h1', 'center', '#635bff', '48px'),
            $this->blockService->text('Your Exclusive Discount', 'center', '#666666'),
            $this->blockService->spacer('30px'),
            $this->blockService->countdown('This Offer Expires In'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Claim Your Discount', '{offer_url}', '#635bff'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('*Limited to the first 100 customers. Cannot be combined with other offers.', 'center', '#999999', '12px'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getBlackFridayBlocks(): array
    {
        return [
            $this->blockService->heading('BLACK FRIDAY', 'h1', 'center', '#ffffff', '48px'),
            $this->blockService->heading('Up to 70% OFF!', 'h2', 'center', '#ffd700', '32px'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('Our biggest sale of the year is HERE! Don\'t miss out on incredible savings across all products.', 'center', '#ffffff'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('SHOP BLACK FRIDAY', '{shop_url}', '#ffd700', '#000000'),
            $this->blockService->spacer('30px'),
            $this->blockService->countdown('Sale Ends In'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getCyberMondayBlocks(): array
    {
        return [
            $this->blockService->heading('CYBER MONDAY', 'h1', 'center', '#00d9ff', '48px'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('Deals Start NOW!', 'h2', 'center', '#333333', '28px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('The biggest online shopping day is here. Score massive discounts on everything you love!', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('START SHOPPING', '{shop_url}', '#00d9ff', '#000000'),
            $this->blockService->spacer('30px'),
            $this->blockService->countdown('Cyber Monday Ends In'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    // ========================================
    // TEMPLATE BLOCKS - Transactional Templates
    // ========================================

    private function getOrderConfirmationBlocks(): array
    {
        return [
            $this->blockService->heading('Order Confirmed! ✓', 'h1', 'center', '#22c55e', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Thank you for your order! We\'re getting it ready for you.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Order #1010123', 'h2', 'left', '#333333', '20px'),
            $this->blockService->divider(),
            $this->blockService->table(
                ['Product', 'Qty', 'Price'],
                [
                    ['Product Name 1', '2', '$29.99'],
                    ['Product Name 2', '1', '$49.99'],
                    ['Shipping', '', '$5.99'],
                    ['<strong>Total</strong>', '', '<strong>$115.96</strong>'],
                ]
            ),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Shipping Address', 'h3', 'left', '#333333', '18px'),
            $this->blockService->text('{customer_name}<br>{shipping_address}<br>{shipping_city}, {shipping_zip}', 'left', '#666666', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Track Your Order', '{tracking_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getShippingNotificationBlocks(): array
    {
        return [
            $this->blockService->heading('Your Order Has Shipped! 📦', 'h1', 'center', '#635bff', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Great news! Your order is on its way. Here are the details:', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('<strong>Tracking Number:</strong> {tracking_number}', 'center'),
            $this->blockService->text('<strong>Carrier:</strong> {carrier_name}', 'center'),
            $this->blockService->text('<strong>Estimated Delivery:</strong> {delivery_date}', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Track Package', '{tracking_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->divider(),
            $this->blockService->text('If you have any questions about your delivery, please don\'t hesitate to contact us.', 'center', '#666666', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getInvoiceBlocks(): array
    {
        return [
            $this->blockService->heading('Invoice', 'h1', 'center', '#333333', '32px'),
            $this->blockService->text('Invoice #1010123', 'center', '#666666'),
            $this->blockService->spacer('30px'),
            $this->blockService->divider(),
            $this->blockService->text('<strong>Bill To:</strong>', 'left'),
            $this->blockService->text('{customer_name}<br>{customer_email}<br>{customer_address}', 'left', '#666666', '14px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('<strong>Date:</strong> {invoice_date}', 'left', '#666666', '14px'),
            $this->blockService->text('<strong>Due Date:</strong> {due_date}', 'left', '#666666', '14px'),
            $this->blockService->spacer('20px'),
            $this->blockService->table(
                ['Description', 'Qty', 'Amount'],
                [
                    ['Service/Product 1', '1', '$99.00'],
                    ['Service/Product 2', '2', '$150.00'],
                    ['<strong>Subtotal</strong>', '', '$249.00'],
                    ['Tax (10%)', '', '$24.90'],
                    ['<strong>Total Due</strong>', '', '<strong>$273.90</strong>'],
                ]
            ),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Pay Invoice', '{payment_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getReceiptBlocks(): array
    {
        return [
            $this->blockService->heading('Payment Receipt', 'h1', 'center', '#22c55e', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Thank you for your payment! Here\'s your receipt.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('<strong>Receipt Number:</strong> {receipt_number}', 'center'),
            $this->blockService->text('<strong>Date:</strong> {payment_date}', 'center'),
            $this->blockService->text('<strong>Amount Paid:</strong> ${amount}', 'center', '#22c55e', '24px'),
            $this->blockService->spacer('30px'),
            $this->blockService->divider(),
            $this->blockService->text('<strong>Payment Method:</strong> {payment_method}', 'left', '#666666', '14px'),
            $this->blockService->text('<strong>Transaction ID:</strong> {transaction_id}', 'left', '#666666', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Download Receipt', '{receipt_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getWeeklyDigestBlocks(): array
    {
        return [
            $this->blockService->heading('📰 Your Weekly Digest', 'h1', 'center', '#333333', '32px'),
            $this->blockService->text('{date}', 'center', '#666666'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('Here\'s what happened this week:', 'center'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('Featured Story', 'h2', 'left', '#635bff', '22px'),
            $this->blockService->text('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
            $this->blockService->button('Read More', '#', '#635bff', '#ffffff', 'left'),
            $this->blockService->spacer('30px'),
            $this->blockService->divider(),
            $this->blockService->heading('More Headlines', 'h2', 'left', '#333333', '22px'),
            $this->blockService->listBlock([
                'Story headline one goes here',
                'Story headline two goes here',
                'Story headline three goes here',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getBlogRoundupBlocks(): array
    {
        return [
            $this->blockService->text('{site_icon_image}', 'center'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('Top Articles This Month', 'h1', 'center', '#333333', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Catch up on the best content from our blog:', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('1. Article Title One', 'h3', 'left', '#635bff', '20px'),
            $this->blockService->text('A brief description of the article content goes here. This gives readers a preview of what to expect.'),
            $this->blockService->spacer('10px'),
            $this->blockService->heading('2. Article Title Two', 'h3', 'left', '#635bff', '20px'),
            $this->blockService->text('A brief description of the article content goes here. This gives readers a preview of what to expect.'),
            $this->blockService->spacer('10px'),
            $this->blockService->heading('3. Article Title Three', 'h3', 'left', '#635bff', '20px'),
            $this->blockService->text('A brief description of the article content goes here. This gives readers a preview of what to expect.'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('View All Articles', '{blog_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getIndustryNewsBlocks(): array
    {
        return [
            $this->blockService->heading('Industry Insights', 'h1', 'center', '#333333', '32px'),
            $this->blockService->text('Your weekly dose of industry news and trends', 'center', '#666666'),
            $this->blockService->spacer('30px'),
            $this->blockService->quote('The industry is evolving rapidly. Here\'s what you need to know to stay ahead of the curve.', 'Industry Expert', 'CEO, Leading Company'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Key Trends This Week', 'h2', 'left', '#333333', '22px'),
            $this->blockService->listBlock([
                'Trend 1: Brief description of the trend',
                'Trend 2: Brief description of the trend',
                'Trend 3: Brief description of the trend',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Read Full Report', '{report_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getCompanyNewsletterBlocks(): array
    {
        return [
            $this->blockService->heading('Company News & Updates', 'h1', 'center', '#333333', '32px'),
            $this->blockService->text('{month} Edition', 'center', '#666666'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('CEO Message', 'h2', 'left', '#635bff', '22px'),
            $this->blockService->text('Dear Team, I\'m excited to share some updates about what\'s been happening at our company this month...'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Company Highlights', 'h2', 'left', '#635bff', '22px'),
            $this->blockService->listBlock([
                'We hit a major milestone this month',
                'New team members joined our family',
                'Exciting partnership announcement coming soon',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Upcoming Events', 'h2', 'left', '#635bff', '22px'),
            $this->blockService->text('• Team Building Event - {event_date}<br>• Quarterly Review - Next Friday<br>• Holiday Party - Coming Soon'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getTipsNewsletterBlocks(): array
    {
        return [
            $this->blockService->heading('💡 Weekly Tips', 'h1', 'center', '#333333', '32px'),
            $this->blockService->text('Boost Your Productivity', 'center', '#666666'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Tip #1: Start Your Day Right', 'h2', 'left', '#635bff', '20px'),
            $this->blockService->text('Begin each morning with a clear plan. Write down your top 3 priorities before checking email.'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('Tip #2: Take Regular Breaks', 'h2', 'left', '#635bff', '20px'),
            $this->blockService->text('Use the Pomodoro technique: 25 minutes of focused work followed by a 5-minute break.'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('Tip #3: Minimize Distractions', 'h2', 'left', '#635bff', '20px'),
            $this->blockService->text('Turn off notifications during deep work sessions. Your focus is your superpower.'),
            $this->blockService->spacer('30px'),
            $this->blockService->quote('Productivity is never an accident. It is always the result of a commitment to excellence.', 'Paul J. Meyer'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getWebinarInvitationBlocks(): array
    {
        return [
            $this->blockService->heading('You\'re Invited! 🎓', 'h1', 'center', '#635bff', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('Mastering Success in {year}', 'h2', 'center', '#333333', '28px'),
            $this->blockService->text('A Free Live Webinar', 'center', '#666666'),
            $this->blockService->spacer('30px'),
            $this->blockService->image('https://placehold.co/600x300/635bff/ffffff?text=Webinar+Image', 'Webinar'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('<strong>Date:</strong> {event_date}', 'center'),
            $this->blockService->text('<strong>Time:</strong> {event_time}', 'center'),
            $this->blockService->text('<strong>Duration:</strong> 60 minutes', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('What You\'ll Learn:', 'h3', 'left', '#333333', '20px'),
            $this->blockService->listBlock([
                'Key strategies for success',
                'Practical tips you can apply immediately',
                'Live Q&A with industry experts',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Reserve Your Spot', '{registration_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getEventReminderBlocks(): array
    {
        return [
            $this->blockService->heading('⏰ Reminder: Tomorrow!', 'h1', 'center', '#ff4757', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Don\'t forget! Mastering Success in {year} starts tomorrow at {time}.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->countdown('Event Starts In'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('<strong>Add to Calendar:</strong>', 'center'),
            $this->blockService->text('Google Calendar | Outlook | iCal', 'center', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Join Event', '{event_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->quote('Pro tip: Join 5 minutes early to test your audio and video!'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getConferenceInviteBlocks(): array
    {
        return [
            $this->blockService->heading('Annual Conference {year}', 'h1', 'center', '#333333', '36px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Join us for the biggest event of the year', 'center', '#666666', '18px'),
            $this->blockService->spacer('30px'),
            $this->blockService->image('https://placehold.co/600x300/635bff/ffffff?text=Conference', 'Conference'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('<strong>📅 Date:</strong> {conference_date}', 'center'),
            $this->blockService->text('<strong>📍 Location:</strong> {conference_venue}', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Featured Speakers', 'h2', 'center', '#333333', '24px'),
            $this->blockService->text('Industry leaders sharing insights on the latest trends and innovations.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Register Now - Early Bird Pricing', '{registration_url}', '#635bff'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Early bird pricing ends {early_bird_deadline}', 'center', '#ff4757', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getVirtualEventBlocks(): array
    {
        return [
            $this->blockService->heading('🌐 Virtual Event', 'h1', 'center', '#635bff', '32px'),
            $this->blockService->heading('Mastering Success in {year}', 'h2', 'center', '#333333', '28px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Join us online from anywhere in the world!', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('<strong>📅 Date:</strong> {event_date}', 'center'),
            $this->blockService->text('<strong>⏰ Time:</strong> {event_time} (Your Local Time)', 'center'),
            $this->blockService->text('<strong>💻 Platform:</strong> Zoom', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Event Highlights:', 'h3', 'left', '#333333', '20px'),
            $this->blockService->listBlock([
                'Keynote presentations from industry experts',
                'Interactive workshops and breakout sessions',
                'Networking opportunities with attendees worldwide',
                'Exclusive resources and materials',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Register Free', '{registration_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getEventThankYouBlocks(): array
    {
        return [
            $this->blockService->heading('Thank You for Attending! 🙏', 'h1', 'center', '#22c55e', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('We hope you enjoyed Mastering Success in {year}. Your participation made the event a success!', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('What\'s Next?', 'h2', 'center', '#333333', '24px'),
            $this->blockService->listBlock([
                'Event recordings will be available within 48 hours',
                'Presentation slides are ready for download',
                'Certificate of attendance is attached',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Access Event Resources', '{resources_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Share Your Feedback', 'h3', 'center', '#333333', '20px'),
            $this->blockService->text('Help us improve! Take our quick 2-minute survey.', 'center'),
            $this->blockService->button('Take Survey', '{survey_url}', '#333333', '#ffffff'),
            $this->blockService->spacer('30px'),
            $this->blockService->social(['twitter' => '#', 'linkedin' => '#']),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    // ========================================
    // TEMPLATE BLOCKS - E-commerce Templates
    // ========================================

    private function getCartAbandonmentBlocks(): array
    {
        return [
            $this->blockService->heading('You Left Something Behind! 🛒', 'h1', 'center', '#333333', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Hi {first_name}, we noticed you didn\'t complete your purchase. Your items are waiting for you!', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->table(
                ['Item', 'Price'],
                [
                    ['Product in Cart 1', '$49.99'],
                    ['Product in Cart 2', '$29.99'],
                ]
            ),
            $this->blockService->spacer('30px'),
            $this->blockService->text('Complete your order now and get <strong>10% OFF</strong> with code: <strong>COMEBACK10</strong>', 'center', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Complete Purchase', '{cart_url}', '#635bff'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Need help? Reply to this email and we\'ll assist you.', 'center', '#666666', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getProductRecommendationBlocks(): array
    {
        return [
            $this->blockService->heading('Picked Just for You', 'h1', 'center', '#333333', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Hi {first_name}, based on your browsing history, we think you\'ll love these:', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->image('https://placehold.co/600x200/f8fafc/333333?text=Product+Recommendations', 'Recommended Products'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Top Picks for You', 'h2', 'left', '#333333', '22px'),
            $this->blockService->listBlock([
                'Product Recommendation 1 - $49.99',
                'Product Recommendation 2 - $79.99',
                'Product Recommendation 3 - $39.99',
            ]),
            $this->blockService->spacer('30px'),
            $this->blockService->button('View All Recommendations', '{recommendations_url}', '#635bff'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getBackInStockBlocks(): array
    {
        return [
            $this->blockService->heading('It\'s Back! 🎉', 'h1', 'center', '#22c55e', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Great news! The item you\'ve been waiting for is back in stock.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->image('https://placehold.co/300x300/f8fafc/333333?text=Product+XYZ', 'Product XYZ'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('Product XYZ', 'h2', 'center', '#333333', '24px'),
            $this->blockService->text('$99.99', 'center', '#635bff', '28px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('⚠️ Limited quantities available. Don\'t miss out this time!', 'center', '#ff4757'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Buy Now', '{product_url}', '#22c55e'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getReviewRequestBlocks(): array
    {
        return [
            $this->blockService->heading('How Was Your Purchase?', 'h1', 'center', '#333333', '32px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Hi {first_name}, we hope you\'re enjoying your recent purchase! We\'d love to hear what you think.', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->image('https://placehold.co/200x200/f8fafc/333333?text=Your+Product', 'Your Product'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('⭐⭐⭐⭐⭐', 'center', '#fbbf24', '32px'),
            $this->blockService->text('Click a star to rate your experience', 'center', '#666666', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Write a Review', '{review_url}', '#635bff'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Your feedback helps us improve and helps other customers make informed decisions.', 'center', '#666666', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }

    private function getBirthdayDiscountBlocks(): array
    {
        return [
            $this->blockService->heading('🎂 Happy Birthday, {first_name}!', 'h1', 'center', '#ff69b4', '36px'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Wishing you an amazing day filled with joy and celebration!', 'center'),
            $this->blockService->spacer('30px'),
            $this->blockService->heading('Here\'s Your Gift!', 'h2', 'center', '#333333', '28px'),
            $this->blockService->spacer('20px'),
            $this->blockService->heading('25% OFF', 'h1', 'center', '#635bff', '56px'),
            $this->blockService->text('Your Birthday Discount', 'center', '#666666'),
            $this->blockService->spacer('30px'),
            $this->blockService->text('Use code: <strong>BDAY25</strong> at checkout', 'center', '#333333', '18px'),
            $this->blockService->spacer('30px'),
            $this->blockService->button('Start Shopping 🎁', '{shop_url}', '#ff69b4'),
            $this->blockService->spacer('20px'),
            $this->blockService->text('Valid for the next 7 days. Enjoy your special day!', 'center', '#666666', '14px'),
            $this->blockService->spacer('30px'),
            $this->blockService->footer('{app_name}'),
        ];
    }
}
