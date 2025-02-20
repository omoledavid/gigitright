<?php

namespace App\Notify;

use Http\Client\Exception;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\UnstructuredHeader;

class Email extends NotifyProcess implements Notifiable
{
    /**
     * Email of receiver
     *
     * @var string
     */
    public $email;

    /**
     * Assign value to properties
     *
     * @return void
     */
    public function __construct()
    {
        $this->statusField = 'email_status';
        $this->body = 'email_body';
        $this->globalTemplate = 'email_template';
        $this->notifyConfig = 'mail_config';
    }

    /**
     * Send notification
     *
     * @return void|bool
     */
    public function send()
    {

        //get message from parent
        $message = $this->getMessage();
        if ($this->setting->en && $message) {
            //Send mail
            $methodName = $this->setting->mail_config->name;
            $method = $this->mailMethods($methodName);
            try {
                $this->$method();
//                $this->createLog('email');
            } catch (\Exception $e) {
                dd($e);
//                $this->createErrorLog($e->getMessage());
//                session()->flash('mail_error', $e->getMessage());
            }
        }
    }

    /**
     * Get the method name
     *
     * @return string
     */
    protected function mailMethods($name)
    {
        $methods = [
            'php' => 'sendPhpMail',
            'smtp' => 'sendSmtpMail',
            'mailtrap' => 'sendMailTrap',
        ];
        return $methods[$name];
    }

    protected function sendPhpMail()
    {
        $general = $this->setting;
        $headers = "From: $general->site_name <$general->email_from> \r\n";
        $headers .= "Reply-To: $general->site_name <$general->email_from> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        @mail($this->email, $this->subject, $this->finalMessage, $headers);
    }

    protected function sendSmtpMail()
    {
        $mail = new PHPMailer(true);
        $general = $this->setting;
        $config = $general->mail_config;
        //Server settings
        $mail->isSMTP();
        $mail->Host = "gigitright.com";
        $mail->SMTPAuth = true;
        $mail->Username = "support@gigitright.com";
        $mail->Password = ",[R%X1+m@@xy";

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        //Recipients
        $mail->setFrom($general->email_from, $general->site_name);
        $mail->addAddress($this->email, $this->receiverName);
        $mail->addReplyTo($general->email_from, $general->site_name);
        // Content
        $mail->isHTML(true);
        $mail->Subject = $this->subject;
        $mail->Body = $this->finalMessage;
        $mail->send();
    }

    public function sendMailTrap()
    {
        try {
            $general = $this->setting;
            $mailtrap = MailtrapClient::initSendingEmails(
                apiKey: env('MAILTRAP_API_KEY') #your API token from here https://mailtrap.io/api-tokens
            );

            $email = (new MailtrapEmail())
                ->from(new Address($general->email_from, $general->site_name)) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
                ->replyTo(new Address($general->email_from))
                ->to(new Address($this->email, $this->user->firstname))
                ->priority(\Symfony\Component\Mime\Email::PRIORITY_HIGH)
                //->cc($general->email_from)
                //->addCc($general->email_from)
                //->bcc($general->email_from)
                ->subject($this->subject)
                //->text('Hey! Learn the best practices of building HTML emails and play with ready-to-go templates. Mailtrap’s Guide on How to Build HTML Email is live on our blog')
                ->html($this->finalMessage)
                //->embed(fopen('https://mailtrap.io/wp-content/uploads/2021/04/mailtrap-new-logo.svg', 'r'), 'logo', 'image/svg+xml')
                //->attachFromPath('README.md')
//                ->customVariables([
//                    'user_id' => '45982',
//                    'batch_id' => 'PSJ-12'
//                ])
                ->category('Integration Test')
            ;

            // Custom email headers (optional)
//            $email->getHeaders()
//                ->addTextHeader('X-Message-Source', 'test.com')
//                ->add(new UnstructuredHeader('X-Mailer', 'Mailtrap PHP Client'))
//            ;

            $response = $mailtrap->send($email);

            //var_dump(ResponseHelper::toArray($response)); // body (array)
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    /**
     * Configure some properties
     *
     * @return void
     */
    public function prevConfiguration()
    {
        if ($this->user) {
            $this->email = $this->user->email;
            $this->receiverName = $this->user->fullname;
        }
        $this->toAddress = $this->email;
    }
}
