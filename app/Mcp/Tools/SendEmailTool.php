<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Attributes\McpToolMeta;
use App\Mcp\Tools\Concerns\InteractsWithMcpAuthorization;
use App\Models\EmailTemplate;
use App\Services\Emails\EmailSender;
use App\Services\Emails\EmailVariable;
use App\Services\Emails\Mailer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('send-email')]
#[Description('Send an email to a recipient using an email template or raw subject/body content.')]
#[McpToolMeta(ability: 'mcp:email.send', permission: 'email_template.edit', group: 'Email')]
class SendEmailTool extends Tool
{
    use InteractsWithMcpAuthorization;

    public function __construct(
        protected EmailVariable $emailVariable,
        protected Mailer $mailer
    ) {
    }

    public function handle(Request $request): Response
    {
        if ($response = $this->authorizeMcpAbility('mcp:email.send', 'email_template.edit')) {
            return $response;
        }

        $validated = $request->validate([
            'to' => ['required', 'email', 'max:255'],
            'template_id' => ['nullable', 'integer', 'min:1', 'required_without_all:subject,body_html'],
            'subject' => ['nullable', 'string', 'max:255', 'required_without:template_id'],
            'body_html' => ['nullable', 'string', 'required_without:template_id'],
            'variables' => ['nullable', 'array'],
        ]);

        try {
            if (! empty($validated['template_id'])) {
                $template = EmailTemplate::query()->find((int) $validated['template_id']);

                if ($template === null) {
                    return Response::error(__('Email template not found.'));
                }

                $variables = array_merge(
                    $this->emailVariable->getPreviewSampleData(),
                    $validated['variables'] ?? [],
                    ['email' => $validated['to']]
                );

                $rendered = $template->renderTemplate($variables);
                $subject = (string) ($rendered['subject'] ?? '');
                $bodyHtml = (string) ($rendered['body_html'] ?? '');
            } else {
                $subject = (string) $validated['subject'];
                $bodyHtml = (string) $validated['body_html'];
                $variables = array_merge(
                    $this->emailVariable->getPreviewSampleData(),
                    $validated['variables'] ?? [],
                    ['email' => $validated['to']]
                );
            }

            $emailSender = app(EmailSender::class);
            $emailSender->setSubject($subject)->setContent($bodyHtml);

            $this->sendMailMessageToRecipient($emailSender, $validated['to'], null, $variables);

            return Response::json([
                'success' => true,
                'message' => __('Email sent successfully.'),
                'to' => $validated['to'],
                'subject' => $subject,
                'template_id' => $validated['template_id'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            Log::error('MCP send-email failed', [
                'error' => $exception->getMessage(),
                'to' => $validated['to'] ?? null,
            ]);

            return Response::error(__('Failed to send email: :message', ['message' => $exception->getMessage()]));
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'to' => $schema->string()
                ->description('Recipient email address.')
                ->required(),
            'template_id' => $schema->integer()
                ->description('Email template ID to render and send.'),
            'subject' => $schema->string()
                ->description('Raw email subject when not using a template.'),
            'body_html' => $schema->string()
                ->description('Raw HTML body when not using a template.'),
            'variables' => $schema->object()
                ->description('Optional template variable overrides, e.g. first_name, last_name.'),
        ];
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function sendMailMessageToRecipient(EmailSender $emailSender, string $recipient, ?string $from, array $variables = []): void
    {
        /** @var MailMessage $mailMessage */
        $mailMessage = $emailSender->getMailMessage($from, $variables);

        $html = (string) $mailMessage->render();
        $subject = (string) $mailMessage->subject;
        $fromEmail = $mailMessage->from[0] ?? config('mail.from.address');
        $fromName = $mailMessage->from[1] ?? config('mail.from.name');
        $replyTo = $mailMessage->replyTo[0] ?? null;

        if (empty($fromEmail) || ! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = 'no-reply@laradashboard.com';
        }

        if (empty($fromName)) {
            $fromName = 'Lara Dashboard';
        }

        $this->mailer->html($html, function ($message) use ($subject, $recipient, $fromEmail, $fromName, $replyTo) {
            $message->to($recipient)
                ->from($fromEmail, $fromName)
                ->subject($subject);

            if (! empty($replyTo)) {
                $message->replyTo($replyTo[0] ?? $replyTo, $replyTo[1] ?? null);
            }
        });
    }
}
