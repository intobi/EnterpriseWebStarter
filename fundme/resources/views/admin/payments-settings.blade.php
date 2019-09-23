@extends('admin.layout')

@section('css')
<link href="{{ asset('public/plugins/iCheck/all.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h4>
            {{ trans('admin.admin') }}
            	<i class="fa fa-angle-right margin-separator"></i>
            		{{ trans('misc.payment_settings') }}

          </h4>

        </section>

        <!-- Main content -->
        <section class="content">

        	 @if(Session::has('success_message'))
		    <div class="alert alert-success">
		    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">×</span>
								</button>
		       <i class="fa fa-check margin-separator"></i> {{ Session::get('success_message') }}
		    </div>
		@endif

        	<div class="content">

        		<div class="row">

        	<div class="box box-danger">
                <div class="box-header with-border">
                  <h3 class="box-title"><strong>{{ trans('misc.payment_settings') }}</strong></h3>
                </div><!-- /.box-header -->

                <!-- form start -->
                <form class="form-horizontal" method="POST" action="{{ url('panel/admin/payments') }}" enctype="multipart/form-data">

                	<input type="hidden" name="_token" value="{{ csrf_token() }}">

					@include('errors.errors-forms')


                      <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('admin.currency_code') }}</label>
                      <div class="col-sm-10">
                      	<select name="currency_code" class="form-control">

                      		<option @if( $settings->currency_code == 'USD' ) selected="selected" @endif value="USD">USD - U.S Dollar</option>
						  	<option @if( $settings->currency_code == 'EUR' ) selected="selected" @endif  value="EUR">EUR - Euro</option>
						  	<option @if( $settings->currency_code == 'GBP' ) selected="selected" @endif value="GBP">GBP - UK</option>
						  	<option @if( $settings->currency_code == 'AUD' ) selected="selected" @endif value="AUD">AUD - Australian Dollar</option>
						  	<option @if( $settings->currency_code == 'JPY' ) selected="selected" @endif value="JPY">JPY - Japanese Yen</option>

						  	<option @if( $settings->currency_code == 'BRL' ) selected="selected" @endif value="BRL">BRL - Brazilian Real</option>
						  	<option @if( $settings->currency_code == 'MXN' ) selected="selected" @endif  value="MXN">MXN - Mexican Peso</option>
						  	<option @if( $settings->currency_code == 'SEK' ) selected="selected" @endif value="SEK">SEK - Swedish Krona</option>
						  	<option @if( $settings->currency_code == 'CHF' ) selected="selected" @endif value="CHF">CHF - Swiss Franc</option>


						  	<option @if( $settings->currency_code == 'SGD' ) selected="selected" @endif value="SGD">SGD - Singapore Dollar</option>
						  	<option @if( $settings->currency_code == 'DKK' ) selected="selected" @endif value="DKK">DKK - Danish Krone</option>
						  	<option @if( $settings->currency_code == 'RUB' ) selected="selected" @endif value="RUB">RUB - Russian Ruble</option>

						  	<option @if( $settings->currency_code == 'CAD' ) selected="selected" @endif value="CAD">CAD - Canadian Dollar</option>
						  	<option @if( $settings->currency_code == 'CZK' ) selected="selected" @endif value="CZK">CZK - Czech Koruna</option>
						  	<option @if( $settings->currency_code == 'HKD' ) selected="selected" @endif value="HKD">HKD - Hong Kong Dollar</option>
						  	<option @if( $settings->currency_code == 'PLN' ) selected="selected" @endif value="PLN">PLN - Polish Zloty</option>
						  	<option @if( $settings->currency_code == 'NOK' ) selected="selected" @endif value="NOK">NOK - Norwegian Krone</option>
                          </select>
                      </div>
                    </div>
                  </div><!-- /.box-body -->

                   <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{ trans('misc.fee_donation') }}</label>
                      <div class="col-sm-10">
                      	<select name="fee_donation" class="form-control">
                      		<option @if( $settings->fee_donation == '1' ) selected="selected" @endif value="1">1%</option>
                      		<option @if( $settings->fee_donation == '2' ) selected="selected" @endif value="2">2%</option>
						  	<option @if( $settings->fee_donation == '3' ) selected="selected" @endif  value="3">3%</option>
						  	<option @if( $settings->fee_donation == '4' ) selected="selected" @endif value="4">4%</option>
						  	<option @if( $settings->fee_donation == '5' ) selected="selected" @endif value="5">5%</option>

						  	<option @if( $settings->fee_donation == '6' ) selected="selected" @endif value="6">6%</option>
						  	<option @if( $settings->fee_donation == '7' ) selected="selected" @endif value="7">7%</option>
						  	<option @if( $settings->fee_donation == '8' ) selected="selected" @endif value="8">8%</option>
						  	<option @if( $settings->fee_donation == '9' ) selected="selected" @endif value="9">9%</option>

						  	<option @if( $settings->fee_donation == '10' ) selected="selected" @endif value="10">10%</option>
						  	<option @if( $settings->fee_donation == '15' ) selected="selected" @endif value="15">15%</option>
                          </select>
                      </div>
                    </div>
                  </div><!-- /.box-body -->

                  <!-- Start Box Body -->
                  <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">{{trans('misc.payments_options')}}</label>
                      <div class="col-sm-10">
                        <div class="radio">
                        <label class="padding-zero">
                          <input type="checkbox" value="1" name="enable_paypal" @if( $settings->enable_paypal == 1 ) checked="checked" @endif>
                          PayPal
                        </label>
                      </div>
                      <div class="radio">
                        <label class="padding-zero">
                          <input type="checkbox" value="1" name="enable_stripe" @if( $settings->enable_stripe == 1 ) checked="checked" @endif>
                          Stripe
                        </label>
                      </div>
                      <div class="radio">
                        <label class="padding-zero">
                          <input type="checkbox" value="1" name="enable_bank_transfer" @if( $settings->enable_bank_transfer == 1 ) checked="checked" @endif>
                          {{trans('misc.bank_transfer')}}
                        </label>
                      </div>
                      </div>
                    </div>
                  </div><!-- /.box-body -->

                  <div class="box-header with-border">
                    <h3 class="box-title"><strong>PayPal</strong></h3>
                  </div><!-- /.box-header -->

                  <!-- Start Box Body -->
               <div class="box-body">
                 <div class="form-group">
                   <label class="col-sm-2 control-label">{{ trans('admin.paypal_account') }}</label>
                   <div class="col-sm-10">
                     <input type="text" value="{{ $settings->paypal_account }}" name="paypal_account" class="form-control" placeholder="{{ trans('admin.paypal_account') }}">
                     <p class="help-block">{{ trans('admin.paypal_account_donations') }}</p>
                   </div>
                 </div>
               </div><!-- /.box-body -->

               <!-- Start Box Body -->
               <div class="box-body">
                 <div class="form-group">
                   <label class="col-sm-2 control-label">PayPal Sandbox</label>
                   <div class="col-sm-10">
                     <div class="radio">
                     <label class="padding-zero">
                       <input type="radio" value="true" name="paypal_sandbox" @if( $settings->paypal_sandbox == 'true' ) checked="checked" @endif checked>
                       On
                     </label>
                   </div>
                   <div class="radio">
                     <label class="padding-zero">
                       <input type="radio" value="false" name="paypal_sandbox" @if( $settings->paypal_sandbox == 'false' ) checked="checked" @endif>
                       Off
                     </label>
                   </div>
                   </div>
                 </div>
               </div><!-- /.box-body -->

               <div class="box-header with-border">
                 <h3 class="box-title"><strong>Stripe</strong></h3>
               </div><!-- /.box-header -->

                 <!-- Start Box Body -->
                 <div class="box-body">
                   <div class="form-group">
                     <label class="col-sm-2 control-label">Stripe Secret Key</label>
                     <div class="col-sm-10">
                       <input type="text" value="{{ $settings->stripe_secret_key }}" name="stripe_secret_key" class="form-control">
                      <p class="help-block"><a href="https://stripe.com/dashboard" target="_blank">https://stripe.com/dashboard</a></p>
                     </div>
                   </div>
                 </div><!-- /.box-body -->

                 <!-- Start Box Body -->
                 <div class="box-body">
                   <div class="form-group">
                     <label class="col-sm-2 control-label">Stripe Publishable Key</label>
                     <div class="col-sm-10">
                       <input type="text" value="{{ $settings->stripe_public_key }}" name="stripe_public_key" class="form-control">
                      <p class="help-block"><a href="https://stripe.com/dashboard" target="_blank">https://stripe.com/dashboard</a></p>
                     </div>
                   </div>
                 </div><!-- /.box-body -->

                 <div class="box-header with-border">
                   <h3 class="box-title"><strong>{{trans('misc.bank_transfer')}}</strong></h3>
                 </div><!-- /.box-header -->


                 <!-- Start Box Body -->
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-2 control-label">{{ trans('misc.bank_swift_code') }}</label>
                  <div class="col-sm-10">
                    <input type="text" value="{{ $settings->bank_swift_code }}" name="bank_swift_code" class="form-control" placeholder="{{ trans('misc.bank_swift_code') }}">
                  </div>
                </div>
              </div><!-- /.box-body -->

              <!-- Start Box Body -->
           <div class="box-body">
             <div class="form-group">
               <label class="col-sm-2 control-label">{{ trans('misc.account_number') }}</label>
               <div class="col-sm-10">
                 <input type="text" value="{{ $settings->account_number }}" name="account_number" class="form-control" placeholder="{{ trans('misc.account_number') }}">
               </div>
             </div>
           </div><!-- /.box-body -->

                 <!-- Start Box Body -->
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-2 control-label">{{ trans('misc.branch_name') }}</label>
                  <div class="col-sm-10">
                    <input type="text" value="{{ $settings->branch_name }}" name="branch_name" class="form-control" placeholder="{{ trans('misc.branch_name') }}">
                  </div>
                </div>
              </div><!-- /.box-body -->

              <!-- Start Box Body -->
           <div class="box-body">
             <div class="form-group">
               <label class="col-sm-2 control-label">{{ trans('misc.branch_address') }}</label>
               <div class="col-sm-10">
                 <input type="text" value="{{ $settings->branch_address }}" name="branch_address" class="form-control" placeholder="{{ trans('misc.branch_address') }}">
               </div>
             </div>
           </div><!-- /.box-body -->

           <!-- Start Box Body -->
        <div class="box-body">
          <div class="form-group">
            <label class="col-sm-2 control-label">{{ trans('misc.account_name') }}</label>
            <div class="col-sm-10">
              <input type="text" value="{{ $settings->account_name }}" name="account_name" class="form-control" placeholder="{{ trans('misc.account_name') }}">
            </div>
          </div>
        </div><!-- /.box-body -->

        <!-- Start Box Body -->
     <div class="box-body">
       <div class="form-group">
         <label class="col-sm-2 control-label">{{ trans('misc.iban') }}</label>
         <div class="col-sm-10">
           <input type="text" value="{{ $settings->iban }}" name="iban" class="form-control" placeholder="{{ trans('misc.iban') }}">
         </div>
       </div>
     </div><!-- /.box-body -->

               <div class="box-footer">
                 <button type="submit" class="btn btn-success">{{ trans('admin.save') }}</button>
               </div><!-- /.box-footer -->
               </form>

              </div><!-- /.row -->

        	</div><!-- /.content -->

          <!-- Your Page Content Here -->

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
@endsection

@section('javascript')

	<!-- icheck -->
	<script src="{{ asset('public/plugins/iCheck/icheck.min.js') }}" type="text/javascript"></script>

	<script type="text/javascript">
		//Flat red color scheme for iCheck
        $('input[type="radio"]').iCheck({
          radioClass: 'iradio_flat-red'
        });

        $('input[type="checkbox"]').iCheck({
    	  	checkboxClass: 'icheckbox_square-red',
        	radioClass: 'iradio_square-red',
    	    increaseArea: '20%' // optional
	  });

	</script>


@endsection
