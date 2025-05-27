<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Events\NotificationCreated;
use App\Models\MailTemplate;
use App\Models\Setting;
use App\Models\Transaction;
use Http\Client\Exception;
use Illuminate\Support\Facades\Cache;
use App\Models\GeneralSetting;
use Illuminate\Support\Str;
use App\Models\User;
use App\Lib\ClientInfo;
use App\Lib\FileManager;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

function gs($key = null)
{
    $general = Cache::get('GeneralSetting');
    if (!$general) {
        $general = GeneralSetting::first();
        Cache::put('GeneralSetting', $general);
    }
    if ($key) {
        return @$general->$key;
    }

    return $general;
}

function notify($user, $templateName, $shortCodes = [], $sendVia = null, $createLog = true, $clickValue = null)
{
    try {
        $template = MailTemplate::query()->where('name', $templateName)->first();
        if (!$template) {
            throw new \Exception("Mail template '$templateName' not found.");
        }

        $globalTemplate = gs('email_template');
        if (!$globalTemplate) {
            throw new \Exception("Global email template not found.");
        }

        // Ensure $shortCodes is always an array
        if (!is_array($shortCodes)) {
            $shortCodes = [];
        }

        $globalShortCodes = [
            '{{fullname}}' => $user->name,
            '{{site_name}}' => gs('site_name'),
        ];

        // Replace placeholders in content
        $content = $template->content;

        foreach ($shortCodes as $key => $value) {
            $content = str_replace($key, $value, $content);
        }


        // Replace placeholders in global template
        $globalTemplate = str_replace(array_keys($globalShortCodes), array_values($globalShortCodes), $globalTemplate);

        // Final email body
        $finalEmailBody = str_replace('{{message}}', $content, $globalTemplate);
        $mailtrap = sendMailTrap($user, $template->subject, $finalEmailBody);
    } catch (\Exception $e) {
        return response($e->getMessage(), 500);
    }
}


function sendMailTrap($user, $subject, $finalMessage)
{
    try {
        $general = gs();

        $mailtrap = MailtrapClient::initSendingEmails(
            apiKey: env('MAILTRAP_API_KEY'),
        );

        $email = (new MailtrapEmail())
            ->from(new Address($general->email_from, $general->site_name))
            ->replyTo(new Address($general->email_from))
            ->to(new Address($user->email, $user->name))
            ->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->html($finalMessage)
            ->category('Integration Test');

        $response = $mailtrap->send($email);


        return $response; // ✅ Return the response for debugging

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


function verificationCode($length)
{
    if ($length == 0) {
        return 0;
    }

    $min = pow(10, $length - 1);
    $max = (int)($min - 1) . '9';
    return random_int($min, $max);
}

function generateUniqueUsername($fullName)
{
    // Start with a username based on the user's full name (e.g., first name + last name)
    $username = Str::slug($fullName); // Convert to slug, e.g., "John Doe" -> "john-doe"

    // Check if the username already exists in the database
    $existingUser = User::where('username', $username)->first();

    // If the username already exists, append a number to make it unique
    $counter = 1;
    while ($existingUser) {
        $newUsername = $username . '-' . $counter; // Add a counter, e.g., "john-doe-1"

        // Check again if this new username exists
        $existingUser = User::where('username', $newUsername)->first();

        // Increment the counter for the next iteration
        $counter++;
    }

    // Return the unique username
    return $existingUser ? $newUsername : $username;
}

function getIpInfo()
{
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}

function osBrowser()
{
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}

function getRealIP()
{
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}

function fileUploader($file, $location, $size = null, $old = null, $thumb = null, $filename = null)
{
    try {
        $fileManager = new FileManager($file);
        $fileManager->path = $location;
        $fileManager->size = $size;
        $fileManager->old = $old;
        $fileManager->thumb = $thumb;
        $fileManager->filename = $filename;
        $fileManager->upload();
        return $fileManager->filename;
    } catch (\Exception $e) {
        // Log the error or handle it as needed
        Log::error('File upload error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

function fileManager()
{
    return new FileManager();
}

function getFilePath($key)
{
    return fileManager()->$key()->path;
}

function getFileSize($key)
{
    return fileManager()->$key()->size;
}

function getFileExt($key)
{
    return fileManager()->$key()->extensions;
}

function resourceStatus($data)
{
    return ($data === 0) ? 'Inactive' : 'Active';
}

function loadValidRelationships($model, $relationship, array $validRelationships)
{
    if (is_array($relationship) && count($relationship) === 1) {
        // Extract and split the relationships
        $relationships = explode(',', $relationship[0]);

        // Filter only valid relationships
        $relationships = array_intersect($relationships, $validRelationships);

        // Load the valid relationships if any
        if (!empty($relationships)) {
            $model->load($relationships);
        }
    }
}
function createTransaction($userId, $transactionType, $amount, $description = '', $paymentMethod = PaymentMethod::WALLET, $currency = 'NGN', $status = 'pending', $source = TransactionSource::WALLET)
{
    return Transaction::create([
        'user_id' => $userId,
        'transaction_type' => $transactionType,
        'amount' => $amount,
        'currency' => $currency,
        'description' => $description,
        'payment_method' => $paymentMethod,
        'status' => $status,
        'transaction_source' => $source,
        'reference' => Str::uuid(), // Generates a unique reference
    ]);
}

function createNotification($userId, $type, $data, $is_read = false)
{
    $notification = Notification::create([
        'user_id' => $userId,
        'type' => $type,
        'data' => [
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? null,
            'url' => $data['url'] ?? null,
            'id' => $data['id'] ?? null,
        ],
        'is_read' => $is_read,
    ]);

    // Dispatch the notification event
    broadcast(new NotificationCreated($notification))->toOthers();

    return $notification;
}
function setting($key, $default = null)
{
    $setting = Setting::where('key', $key)->first();
    return $setting ? $setting->value : $default;
}

function set_setting($key, $value)
{
    return Setting::updateOrCreate(['key' => $key], ['value' => $value]);
}
