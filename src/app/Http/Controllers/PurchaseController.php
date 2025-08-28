<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Models\Item;
use App\Models\User;
use App\Models\SoldItem;
use App\Models\Profile;
use Stripe\StripeClient;

class PurchaseController extends Controller
{
    public function index($item_id, Request $request){
        $item = Item::find($item_id);
        $user = User::find(Auth::id());
        return view('purchase',compact('item','user'));
    }

    public function purchase($item_id, Request $request)
    {
        $item = Item::findOrFail($item_id);
        $stripe = new \Stripe\StripeClient(config('stripe.stripe_secret_key'));

        $checkout_session = $stripe->checkout->sessions->create([
            'payment_method_types' => [$request->payment_method],
            'line_items' => [[
                'price_data' => [
                'currency' => 'jpy',
                'product_data' => ['name' => $item->name],
                'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('checkout.success'),
            'cancel_url'  => route('checkout.cancel'),
            'metadata' => [
                'user_id' => Auth::id(),
                'item_id' => $item->id,
                'sending_postcode' => $request->destination_postcode,
                'sending_address' => $request->destination_address,
                'sending_building' => $request->destination_building,
            ],
        ]);

        return redirect($checkout_session->url);
    }


    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            SoldItem::create([
                'user_id' => $session->metadata->user_id,
                'item_id' => $session->metadata->item_id,
                'sending_postcode' => $session->metadata->sending_postcode,
                'sending_address' => $session->metadata->sending_address,
                'sending_building' => $session->metadata->sending_building,
            ]);
        }

        return response('Webhook handled', 200);
    }

    public function address($item_id, Request $request){
        $user = User::find(Auth::id());
        return view('address', compact('user','item_id'));
    }

    public function updateAddress(AddressRequest $request){

        $user = User::find(Auth::id());
        Profile::where('user_id', $user->id)->update([
            'postcode' => $request->postcode,
            'address' => $request->address,
            'building' => $request->building
        ]);

        return redirect()->route('purchase.index', ['item_id' => $request->item_id]);
    }
}
