<?php $settings = App\Models\AdminSettings::first(); ?>
@extends('app')

@section('title')
{{ trans('auth.sign_up') }} -
@stop

@section('css')
<link href="{{ asset('public/plugins/iCheck/all.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<div class="jumbotron md index-header jumbotron_set jumbotron-cover">
      <div class="container wrap-jumbotron position-relative">
        <h1 class="title-site">{{ trans('auth.sign_up') }}</h1>
        <p class="subtitle-site"><strong>{{$settings->title}}</strong></p>
      </div>
    </div>

<div class="container margin-bottom-40">

	<div class="row">
<!-- Col MD -->
<div class="col-md-12">

	<h2 class="text-center position-relative">{{ trans('auth.sign_up') }}</h2>

	<div class="login-form">

		@if (session('notification'))
						<div class="alert alert-success text-center">

							<div class="btn-block text-center margin-bottom-10">
								<i class="glyphicon glyphicon-ok ico_success_cicle"></i>
								</div>

							{{ session('notification') }}
						</div>
					@endif

		@include('errors.errors-forms')

          	<form action="{{ url('register') }}" method="post" name="form" id="signup_form">

            <input type="hidden" name="_token" value="{{ csrf_token() }}">


            <!-- FORM GROUP -->
            <div class="form-group has-feedback">
              <input type="text" class="form-control login-field custom-rounded" value="{{ old('name') }}" name="name" placeholder="{{ trans('users.name') }}" title="{{ trans('users.name') }}" autocomplete="off">
              <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div><!-- ./FORM GROUP -->

             <!-- FORM GROUP -->
            <div class="form-group has-feedback">
              <input type="text" class="form-control login-field custom-rounded" value="{{ old('email') }}" name="email" placeholder="{{ trans('auth.email') }}" title="{{ trans('auth.email') }}" autocomplete="off">
              <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div><!-- ./FORM GROUP -->


         <!-- FORM GROUP -->
         <div class="form-group has-feedback">
              <input type="password" class="form-control login-field custom-rounded" name="password" placeholder="{{ trans('auth.password') }}" title="{{ trans('auth.password') }}" autocomplete="off">
              <span class="glyphicon glyphicon-lock form-control-feedback"></span>
         </div><!-- ./FORM GROUP -->

         <div class="form-group has-feedback">
			<input type="password" class="form-control" name="password_confirmation" placeholder="{{ trans('auth.confirm_password') }}" title="{{ trans('auth.confirm_password') }}" autocomplete="off">
			<span class="glyphicon glyphicon-log-in form-control-feedback"></span>
		</div>

		<!-- FORM GROUP -->
         <div class="form-group has-feedback">
              <select name="countries_id" class="form-control" >
                      		<option value="">{{trans('misc.select_your_country')}}</option>
                      	@foreach(  App\Models\Countries::orderBy('country_name')->get() as $country )
                            <option value="{{$country->id}}">{{ $country->country_name }}</option>
						@endforeach
                          </select>
         </div><!-- ./FORM GROUP -->

		@if( $settings->captcha == 'on' )
            <div class="form-group has-feedback">
              <input type="text" class="form-control login-field" name="captcha" id="lcaptcha" placeholder="" title="">
              <span class="glyphicon glyphicon-lock form-control-feedback"></span>

              <div class="alert alert-danger btn-sm margin-top-alert" id="errorCaptcha" role="alert" style="display: none;">
            		<strong><i class="glyphicon glyphicon-alert myicon-right"></i> {{Lang::get('auth.error_captcha')}}</strong>
            	</div>
            </div>
            @endif

            <div class="row margin-bottom-15">
              	<div class="col-xs-11">
              		<div class="checkbox icheck margin-zero">
         				<label class="margin-zero">
         					<input @if( old('agree_gdpr') ) checked="checked" @endif class="no-show" name="agree_gdpr" type="checkbox" value="1">
         					<span class="keep-login-title">{{ trans('admin.i_agree_gdpr') }}</span>
                  @if($settings->link_privacy != '')
                    <a href="{{$settings->link_privacy}}" target="_blank">{{ trans('admin.privacy_policy') }}</a>
                  @endif
         			</label>
         		</div>
              	</div>
              </div><!-- row -->

           <button type="submit" id="buttonSubmit" class="btn btn-block btn-lg btn-main custom-rounded">{{ trans('auth.sign_up') }}</button>

           @if(config('services.facebook.client_id') != 'APP_ID' && config('services.facebook.client_id') != '')
           <span class="login-link auth-social" id="twitter-btn-text">{{ trans('auth.or_sign_in_with') }}</span>

               <div class="facebook-login auth-social" id="twitter-btn">
                 <a href="{{url('oauth/facebook')}}" class="btn btn-block btn-lg fb-button custom-rounded" style="color:white;"><i class="fa fa-facebook"></i> Facebook</a>
               </div>
             @endif

          </form>
     </div><!-- Login Form -->

 </div><!-- /COL MD -->

</div><!-- ROW -->

 </div><!-- row -->

 <!-- container wrap-ui -->

@endsection

@section('javascript')

  <script src="{{ asset('public/plugins/iCheck/icheck.min.js') }}"></script>

	<script type="text/javascript">

 @if( $settings->captcha == 'on' )
/*
 *  ==============================================  Captcha  ============================== * /
 */
   var captcha_a = Math.ceil( Math.random() * 5 );
   var captcha_b = Math.ceil( Math.random() * 5 );
   var captcha_c = Math.ceil( Math.random() * 5 );
   var captcha_e = ( captcha_a + captcha_b ) - captcha_c;

function generate_captcha( id ) {
	var id = ( id ) ? id : 'lcaptcha';
	$("#" + id ).html( captcha_a + " + " + captcha_b + " - " + captcha_c + " = ").attr({'placeholder' : captcha_a + " + " + captcha_b + " - " + captcha_c, title: 'Captcha = '+captcha_a + " + " + captcha_b + " - " + captcha_c });
}
$("input").attr('autocomplete','off');
generate_captcha('lcaptcha');

$(document).on('click','#buttonSubmit', function(e){
   	e.preventDefault();
   	var captcha        = $("#lcaptcha").val();
    	if( captcha != captcha_e ){
    		$('.wrap-loader').hide();
				var error = true;
		        $("#errorCaptcha").fadeIn(500);
		        $('#lcaptcha').focus();

		        return false;
		      } else {
		      	$(this).css('display','none');
    			$('.auth-social').css('display','none');
    			$('<div class="btn-block text-center"><i class="fa fa-cog fa-spin fa-3x fa-fw fa-loader"></i></div>').insertAfter('#signup_form');
   				$('#signup_form').submit();
		      }
    });

    @else

	$('#buttonSubmit').click(function(){
    	$(this).css('display','none');
    	$('.auth-social').css('display','none');
    	$('<div class="btn-block text-center"><i class="fa fa-cog fa-spin fa-3x fa-fw fa-loader"></i></div>').insertAfter('#signup_form');
    });

    @endif

    @if (count($errors) > 0)
    	scrollElement('#dangerAlert');
    @endif

    @if (session('notification'))
    	$('#signup_form, #dangerAlert').remove();
    @endif

    $(document).ready(function(){
  	  $('input').iCheck({
  	  	checkboxClass: 'icheckbox_square-red',
  	  });
  	});

</script>


@endsection
