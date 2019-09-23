<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\Campaigns;
use App\Models\Donations;
use App\Models\User;
use App\Models\Rewards;
use Fahim\PaypalIPN\PaypalIPNListener;
use App\Helper;
use Mail;
use Carbon\Carbon;

class DonationsController extends Controller
{
	public function __construct( AdminSettings $settings, Request $request) {
		$this->settings = $settings::first();
		$this->request = $request;
	}

    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id, $slug = null)
    {

	   $response = Campaigns::where('id',$id)->where('status','active')->firstOrFail();

		 $pledgeID = $this->request->input('pledge');

		 $findPledge = $response->rewards->find($pledgeID);

		 if( isset( $findPledge ) ) {
			 $pledgeClaimed = $response->donations()->where('rewards_id',$findPledge->id)->count();
		 }

		 if( isset( $findPledge ) && $pledgeClaimed < $findPledge->quantity ) {
			 $pledge = $findPledge;
		 } else {
			  $pledge = null;
		 }

	   $timeNow = strtotime(Carbon::now());

		if( $response->deadline != '' ) {
		    $deadline = strtotime($response->deadline);
		}

	   // Redirect if campaign is ended
	   if( !isset( $deadline ) && $response->finalized == 1 ) {
	   	 return redirect('campaign/'.$response->id);
	   } else if(  isset( $deadline ) && $response->finalized == 1 ) {
	   	return redirect('campaign/'.$response->id);
	   } else if(  isset( $deadline ) && $deadline < $timeNow ) {
	   	return redirect('campaign/'.$response->id);
	   }

		$uriCampaign = $this->request->path();

		if( str_slug( $response->title ) == '' ) {

				$slugUrl  = '';
			} else {
				$slugUrl  = '/'.str_slug( $response->title );
			}

			$url_campaign = 'donate/'.$response->id.$slugUrl;

			//<<<-- * Redirect the user real page * -->>>
			$uriCanonical = $url_campaign;

			if( $uriCampaign != $uriCanonical ) {
				return redirect($uriCanonical);
			}

		return view('default.donate')->with(['response' => $response, 'pledge' => $pledge]);
    }// End Method

    public function send(){

			$campaign = Campaigns::findOrFail($this->request->_id);

			//<---- Verify Pledge send
			if( isset($this->request->_pledge) ){
				$findPledge = $campaign->rewards->where('id',$this->request->_pledge)
				->where('campaigns_id',$this->request->_id)
				->where('amount',$this->request->amount)->first();
				$pledgeClaimed = $campaign->donations()->where('rewards_id',$findPledge->id)->count();
			}

			if( isset($findPledge) && $pledgeClaimed < $findPledge->quantity) {
				$pledge = $findPledge->id;
			} else {
				$pledge = 0;
			}

			if($this->settings->currency_position == 'right') {
				$currencyPosition =  2;
			} else {
				$currencyPosition =  null;
			}

			$messages = array (
			'amount.min' => trans('misc.amount_minimum'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
			'amount.max' => trans('misc.amount_maximum'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
		);

		//<---- Validation
		$validator = Validator::make($this->request->all(), [
				'amount' => 'required|integer|min:'.$this->settings->min_donation_amount.'|max:'.$this->settings->max_donation_amount,
        'full_name'     => 'required|max:25',
        'email'     => 'required|email|max:100',
        'country'     => 'required',
        'postal_code'     => 'required|max:30',
        'comment'     => 'nullable|max:100',
				'payment_gateway' => 'required|in:paypal,stripe,bank_transfer'
	    	],$messages);

			if ($validator->fails()) {
			        return response()->json([
					        'success' => false,
					        'errors' => $validator->getMessageBag()->toArray(),
					    ]);
			    }

					if($this->request->comment == ''){
						$this->request->comment = '';
					}

					// PayPal
					if( $this->request->payment_gateway == 'paypal' )
					{
						return $this->paypal($campaign,$pledge);
					}

					// Stripe
					if( $this->request->payment_gateway == 'stripe' )
					{
						return $this->stripe($campaign,$pledge);
					}

					// Bank Transfer
					if( $this->request->payment_gateway == 'bank_transfer' )
					{
						return $this->bankTransfer($campaign,$pledge);
					}

}//<--------- End Method  Send

protected function stripe($campaign,$pledge){

	$email    = $this->request->email;
	$cents    = bcmul($this->request->amount, 100);
	$amount = (int)$cents;
	$currency_code = $this->settings->currency_code;
	$description = trans('misc.donation_for').' '.$campaign->title;
	$nameSite = $this->settings->title;


	if( isset( $this->request->stripeToken ) ) {

			\Stripe\Stripe::setApiKey($this->settings->stripe_secret_key);

			// Get the credit card details submitted by the form
			$token = $this->request->stripeToken;

			// Create a charge: this will charge the user's card
			try {
				$charge = \Stripe\Charge::create(array(
					"amount" => $amount, // Amount in cents
					"currency" => strtolower($currency_code),
					"source" => $token,
					"description" => $description
					));

				if( !isset( $this->request->anonymous ) ) {
					$this->request->anonymous = '0';
				}

				// Insert DB and send Mail
				$sql                   = new Donations;
				$sql->campaigns_id     = $campaign->id;
				$sql->txn_id           = 'null';
				$sql->fullname         = $this->request->full_name;
				$sql->email            = $this->request->email;
				$sql->country          = $this->request->country;
				$sql->postal_code      = $this->request->postal_code;
				$sql->donation         = $this->request->amount;
				$sql->payment_gateway  = 'Stripe';
				$sql->comment          = $this->request->comment;
				$sql->anonymous        = $this->request->anonymous;
				$sql->rewards_id       = $pledge;
				$sql->save();

				$this->sendEmail($campaign);

		return response()->json([
							'success' => true,
							'stripeSuccess' => true,
							'url' => url('paypal/donation/success',$campaign->id)
					]);

			} catch(\Stripe\Error\Card $e) {
				// The card has been declined
			}
	} else {
		return response()->json([
							'success' => true,
							'stripeTrue' => true,
							"key" => $this->settings->stripe_public_key,
							"email" => $email,
							 "amount" => $amount,
						"currency" => strtoupper($currency_code),
						"description" => $description,
						"name" => $nameSite
					]);
	}
}//<----- End Method stripe()

  protected function bankTransfer($campaign, $pledge)
	{
		//<---- Validation
		$validator = Validator::make($this->request->all(), [
			'bank_swift_code' => 'required',
			'account_number' => 'required',
			'branch_name' => 'required',
			'branch_address' => 'required',
			'account_name' => 'required',
			'iban' => 'required'
	    	]);

			if ($validator->fails()) {
			        return response()->json([
					        'success' => false,
					        'errors' => $validator->getMessageBag()->toArray(),
					    ]);
			    }

					if( !isset( $this->request->anonymous ) ) {
						$this->request->anonymous = '0';
					}

					// Insert DB and send Mail
					$sql                   = new Donations;
					$sql->campaigns_id     = $campaign->id;
					$sql->txn_id           = 'null';
					$sql->fullname         = $this->request->full_name;
					$sql->email            = $this->request->email;
					$sql->country          = $this->request->country;
					$sql->postal_code      = $this->request->postal_code;
					$sql->donation         = $this->request->amount;
					$sql->payment_gateway  = 'Bank Transfer';
					$sql->comment          = $this->request->comment;
					$sql->anonymous        = $this->request->anonymous;
					$sql->rewards_id       = $pledge;
					$sql->bank_swift_code  = $this->request->bank_swift_code;
					$sql->account_number   = $this->request->account_number;
					$sql->branch_name      = $this->request->branch_name;
					$sql->branch_address   = $this->request->branch_address;
					$sql->account_name     = $this->request->account_name;
					$sql->iban             = $this->request->iban;
					$sql->approved         = '0';
					$sql->save();

					$this->sendEmail($campaign);

			return response()->json([
								'success' => true,
								'bankTransfer' => true,
								'url' => url('paypal/donation/success',$campaign->id)
						]);

	}//<----- End Method bankTransfer

	protected function paypal($campaign, $pledge)
	{

			if ( $this->settings->paypal_sandbox == 'true') {
				// SandBox
				$action = "https://www.sandbox.paypal.com/cgi-bin/webscr";
				} else {
				// Real environment
				$action = "https://www.paypal.com/cgi-bin/webscr";
				}

			$urlSuccess = url('paypal/donation/success',$campaign->id);
			$urlCancel   = url('paypal/donation/cancel',$campaign->id);
			$urlPaypalIPN = url('paypal/ipn');

			return response()->json([
					        'success' => true,
					        'formPP' => '<form id="form_pp" name="_xclick" action="'.$action.'" method="post"  style="display:none">
					        <input type="hidden" name="cmd" value="_donations">
					        <input type="hidden" name="return" value="'.$urlSuccess.'">
					        <input type="hidden" name="cancel_return"   value="'.$urlCancel.'">
					        <input type="hidden" name="notify_url" value="'.$urlPaypalIPN.'">
					        <input type="hidden" name="currency_code" value="'.$this->settings->currency_code.'">
					        <input type="hidden" name="amount" id="amount" value="'.$this->request->amount.'">
					        <input type="hidden" name="custom" value="id='.$campaign->id.'&fn='.$this->request->full_name.'&mail='.$this->request->email.'&cc='.$this->request->country.'&pc='.$this->request->postal_code.'&cm='.$this->request->comment.'&anonymous='.$this->request->anonymous.'&pl='.$pledge.' ">
					        <input type="hidden" name="item_name" value="'.trans('misc.donation_for').' '.$campaign->title.'">
					        <input type="hidden" name="business" value="'.$this->settings->paypal_account.'">
					        <input type="submit">
					        </form>',
					    ]);

	}//<------ End Method paypal()


    public function paypalIpn(){

		$ipn = new PaypalIPNListener();

		$ipn->use_curl = false;

		if ( $this->settings->paypal_sandbox == 'true') {
			// SandBox
			$ipn->use_sandbox = true;
			} else {
			// Real environment
			$ipn->use_sandbox = false;
			}

	    $verified = $ipn->processIpn();

		//$report = Helper::checkTextDb($ipn->getTextReport()); // Report the transation

		$custom  = $_POST['custom'];
		parse_str($custom, $donation);

		$payment_status = $_POST['payment_status'];
		$txn_id               = $_POST['txn_id'];
		$amount             = $_POST['mc_gross'];


	    if ($verified) {
	        if($payment_status == 'Completed'){
	          // Check outh POST variable and insert in DB

	          $verifiedTxnId = Donations::where('txn_id',$txn_id)->first();

			if( !isset( $verifiedTxnId ) ) {

				$sql = new Donations;
		          $sql->campaigns_id = $donation['id'];
				  $sql->txn_id = $txn_id;
				  $sql->fullname = $donation['fn'];
				  $sql->email = $donation['mail'];
				  $sql->country = $donation['cc'];
				  $sql->postal_code = $donation['pc'];
				  $sql->donation = $amount;
				  $sql->payment_gateway = 'PayPal';
				  $sql->comment = $donation['cm'];
				  $sql->anonymous = $donation['anonymous'];
					$sql->rewards_id = $donation['pl'];
				  $sql->save();

				  $sender        = $this->settings->email_no_reply;
				  $titleSite     = $this->settings->title;
				  $_emailUser    = $donation['mail'];
				  $campaignID   = $donation['id'];
				  $fullNameUser = $donation['fn'];

				  Mail::send('emails.thanks-donor', array( 'data' => $campaignID, 'fullname' => $fullNameUser, 'title_site' => $titleSite ),
					function($message) use ( $sender, $fullNameUser, $titleSite, $_emailUser)
						{
						    $message->from($sender, $titleSite)
						    	->to($_emailUser, $fullNameUser)
								->subject( trans('misc.thanks_donation').' - '.$titleSite );
						});
			}// <--- Verified Txn ID

	      } // <-- Payment status
	    } else {
	    	//Some thing went wrong in the payment !
	    }

    }//<----- End Method paypalIpn()

		protected function sendEmail($campaign)
		{
			$sender       = $this->settings->email_no_reply;
			$titleSite    = $this->settings->title;
			$_emailUser   = $this->request->email;
			$campaignID   = $campaign->id;
			$campaignTitle = $campaign->title;
			$organizerName = $campaign->user()->name;
			$organizerEmail = $campaign->user()->email;
			$fullNameUser = $this->request->fullname;
			$paymentGateway = $this->request->payment_gateway;

			Mail::send('emails.thanks-donor', array(
						'data' => $campaignID,
						'fullname' => $fullNameUser,
						'title_site' => $titleSite,
						'campaign_id' => $campaignID,
						'organizer_name' => $organizerName,
						'organizer_email' => $organizerEmail,
						'payment_gateway' => $paymentGateway,
					),
			function($message) use ( $sender, $fullNameUser, $titleSite, $_emailUser, $campaignTitle)
				{
						$message->from($sender, $titleSite)
							->to($_emailUser, $fullNameUser)
						->subject( trans('misc.thanks_donation').' - '.$campaignTitle.' || '.$titleSite );
				});
		}

}
