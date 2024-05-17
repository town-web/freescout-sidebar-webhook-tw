<?php

namespace Modules\SidebarWebhook\Http\Controllers;

use App\Mailbox;
use App\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SidebarWebhookController extends Controller
{
    /**
     * Edit ratings.
     * @return Response
     */
    public function mailboxSettings($id)
    {
        $mailbox = Mailbox::findOrFail($id);

        return view('sidebarwebhook::mailbox_settings', [
            'settings' => [
                'sidebarwebhook.url' => \Option::get('sidebarwebhook.url')[(string) $id] ?? '',
                'sidebarwebhook.secret' => \Option::get('sidebarwebhook.secret')[(string) $id] ?? '',
            ],
            'mailbox' => $mailbox
        ]);
    }

    public function mailboxSettingsSave($id, Request $request)
    {
        $mailbox = Mailbox::findOrFail($id);

        $settings = $request->settings ?: [];

        $urls = \Option::get('sidebarwebhook.url') ?: [];
        $secrets = \Option::get('sidebarwebhook.secret') ?: [];

        $urls[(string) $id] = $settings['sidebarwebhook.url'] ?? '';
        $secrets[(string) $id] = $settings['sidebarwebhook.secret'] ?? '';

        \Option::set('sidebarwebhook.url', $urls);
        \Option::set('sidebarwebhook.secret', $secrets);

        \Session::flash('flash_success_floating', __('Settings updated'));

        return redirect()->route('mailboxes.sidebarwebhook', ['id' => $id]);
    }

    /**
     * Ajax controller.
     */
    public function ajax(Request $request)
    {
        $response = [
            'status' => 'error',
            'msg' => '', // this is error message
        ];
        // mailbox_id and customer_id are required.
        if (!$request->mailbox_id || !$request->conversation_id) {
            $response['msg'] = 'Missing required parameters';
        }

        try {
            $mailbox = Mailbox::findOrFail($request->mailbox_id);
            $conversation = Conversation::findOrFail($request->conversation_id);
            $customer = $conversation->customer;
        } catch (\Exception $e) {
            $response['msg'] = 'Invalid mailbox or customer';
        }

        $url = \Option::get('sidebarwebhook.url')[(string) $mailbox->id] ?? '';
        $secret = \Option::get('sidebarwebhook.secret')[(string) $mailbox->id] ?? '';
        if (!$url) {
            $response['msg'] = 'API Base URL is not set';
        }

        switch ($request->action) {

            case 'loadSidebar':
                $payload = [
                    'customerEmail' => $customer->getMainEmail(),
                    'customerPhones' => $customer->getPhones(),
                    'conversationSubject' => $conversation->getSubject(),
                    'conversationType' => $conversation->getTypeName(),
                    'mailboxId' => $mailbox->id,
                    'secret' => empty($secret) ? '' : $secret,
                ];

                try {
                    $client = new \GuzzleHttp\Client();
                    $result = $client->post($url . '/hook', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'text/html',
                        ],
                        'query' => [
                            'token' => $secret
                        ],
                        'body' => json_encode($payload),
                    ]);
                    $response['html'] = $result->getBody()->getContents();
                    $response['status'] = 'success';
                } catch (\Exception $e) {
                    $response['msg'] = 'Webhook error: ' . $e->getMessage();
                    break;
                }

                break;

            case 'searchClient':
                try {
                    $client = new \GuzzleHttp\Client();
                    $result = $client->request('GET', $url . '/global/search', [
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],
                        'query' => [
                            'search' => $request->search,
                            'token' => $secret
                        ]
                    ]);
                    $response['data'] = $result->getBody()->getContents();
                    $response['user'] = ["email" => $customer->getMainEmail(), 'phone' => $customer->getPhones()];
                    $response['status'] = 'success';
                } catch (\Exception $e) {
                    $response['msg'] = 'Global search error: ' . $e->getMessage();
                    break;
                }

                break;

            case 'addClientContact':
                try {
                    $client = new \GuzzleHttp\Client();
                    $result = $client->request('POST', $url . '/client-contacts', [
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],
                        'query' => [
                            'token' => $secret
                        ],
                        'body' => json_encode($request->data),
                    ]);
                    $response['data'] = $result->getBody()->getContents();
                    $response['user'] = ["email" => $customer->getMainEmail(), 'phone' => $customer->getPhones()];
                    $response['status'] = 'success';
                } catch (\Exception $e) {
                    $response['msg'] = 'Global search error: ' . $e->getMessage();
                    break;
                }

                break;

            default:
                $response['msg'] = 'Unknown action';
                break;
        }

        if ($response['status'] == 'error' && empty($response['msg'])) {
            $response['msg'] = 'Unknown error occured';
        }

        return \Response::json($response);
    }
}
