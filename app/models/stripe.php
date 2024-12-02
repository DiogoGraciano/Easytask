<?php

namespace app\models;

use Stripe\StripeClient;
use app\enums\stripeInterval;
use app\enums\stripeMode;

class stripe
{
    private StripeClient $stripe;
    private array $itens = [];

    public function __construct()
    {
        $config = (new config)->get(1);

        \Stripe\Stripe::setApiKey($config->stripe_secret_key);
        $this->stripe = new \Stripe\StripeClient($config->stripe_secret_key); 
    }

    public function addItem(string $name,float $value,?stripeInterval $interval = stripeInterval::MONTH,int $qtd = 1)
    {
        $this->itens[] = [
            'price_data' => [
                'currency' => 'brl',
                'product_data' => [
                    'name' => $name
                ],
                'unit_amount' => number_format($value,2,"",""),
            ],
            'quantity' => $qtd
        ];

        if($interval){
            $this->itens[array_key_last($this->itens)]['price_data']['recurring'] = ['interval' => $interval->value];
        }
    }

    public function createSection(string $user_email, string $success_url,string $cancel_url,stripeMode $mode = stripeMode::SUBSCRIPTION,int $trial_days = 0):object
    {
        $params = [
            'customer_email' => $user_email,
            'success_url' => $success_url.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancel_url,
            'line_items' => [
                $this->itens
            ],
            'mode' => $mode->value,
        ];

        if($trial_days){
            $params['subscription_data'] = ['trial_period_days' => 7];
        }

        return $this->stripe->checkout->sessions->create($params);
    }

    public function getSection(string $id_section):object
    {
        return \Stripe\Checkout\Session::retrieve($id_section);
    }

    private function getSubscriptions(string $user_id_stripe):object
    {
        return $this->stripe->subscriptions->all(['customer' => $user_id_stripe]);
    }

    public function subscriptionCancelAll(string $user_id_stripe):array
    {
        $return = [];

        $subscriptions = $this->getSubscriptions($user_id_stripe);

        if(isset($subscriptions["data"])){
            foreach ($subscriptions["data"] as $subscription){
                if(isset($subscription->id)){
                    $s = $this->stripe->subscriptions->cancel($subscription->id, []);
                    if(isset($s->id,$s->status))
                        $return[$s->id] = $s->status;
                }
            }
        }

        return $return;
    }

    public function subscriptionCancel($id_subscription):object
    {
        return $this->stripe->subscriptions->cancel($id_subscription, []);
    }
}
