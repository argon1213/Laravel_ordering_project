<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>@lang('placeorder.title')</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="author" content="colorlib.com">

		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
		<!-- MATERIAL DESIGN ICONIC FONT -->
		<link rel="stylesheet" href="{{ asset('vendor/placeorder/fonts/material-design-iconic-font/css/material-design-iconic-font.css') }}">
		<!-- STYLE CSS -->
		<link rel="stylesheet" href="{{ asset('vendor/placeorder/css/style.css') }}">

		<style>
			.product-qty{
				background:#f3d4b7;
				border-color: #f3d4b7;
				color:white;
				height: 25px;
				width: 25px;
			}

			.product-qty-input{
				margin:	0;
				padding: 0;
				height: 25px;
				width: 30px;
				border: none;
				border-color: transparent;
				text-align: center;
			}

			.error{
				font-size: 10px;
				color: #8a1f11;
			}

			.steps ul li.error a {
				background-color: #8a1f11;
			}

			input[type="text"]:disabled {
			  background: #dddddd;
			}

			select:disabled {
			  background: #dddddd;
			}

			.preview-title {
				font-weight: bold;
			}

			.success-title {
			    font-size: 22px;
			    font-family: Poppins-SemiBold;
			    color: rgb(51, 51, 51);
					margin-top: -62px;
					margin-bottom: 30px;
			}
				@media (min-width: 1024px) {
					.success-title {
						margin-top: -38px;
					}
				}
		</style>
	</head>
	<body>
		<div class="wrapper">
			<div class="image-holder">
				<img src="{{ asset('vendor/placeorder/images/form-wizard.png') }}" alt="">
			</div>
			@if (Session::has('success'))
				<div id="wizard">
					<!-- Place Order Success -->
					<div class="success-title">Place Order Success <br/> 訂單已成功建立</div>
					<div class="container">
						<div class="row">
							<div class="col-12">
								<label id="payment-success-description">
									<!-- You can view your order by below url. -->
									{{ Session::get('success') }}
								</label>
								@if (Session::has('qr_code_url'))
									<div class="visible-print text-center">
										{!! QrCode::size(200)->generate(Session::get('qr_code_url')); !!}
									</div>
								@else
									You can view your order by below url:
									<br/>
									<a href="{{ route('somsclient.login') }}">{{ route('somsclient.login') }}</a>
									<br/>
								@endif
							</div>
						</div>
					</div>
				</div>
			@elseif (Session::has('error'))
				<div id="wizard">
					<!-- Place Order Success -->
					<div class="success-title">Place Order Success <br/> 訂單建立出現問題</div>
					<div class="container">
						<div class="row">
							<div class="col-12">
								<label id="payment-success-description">
									<!-- You can view your order by below url. -->
									{{ Session::get('error') }}
								</label>
							</div>
						</div>
					</div>
				</div>
			@else
				<form id="placeorder" action="{{ route('order-submit') }}" method="post">
					@csrf
					<div id="wizard">
						<!-- SECTION 1 - Access Code -->
						<h4></h4>
						<section>
							<div class="form-row">
								<label for="">
									優惠券代碼 Coupon Code
								</label>
								<input id="couponCode" type="text" class="form-control" placeholder="Please enter your coupon code.">
							</div>
						</section>
						<!-- SECTION 2 - Your Order -->
						<h4></h4>
						<section>
							<div class="product">
								@foreach($items as $item)
									<div class="item product-item" data-id="{{ $item->id }}" data-category="{{ $item->category }}">
										<div class="left">
											<!-- <a href="#" class="thumb">
												<img src="{{ asset('storage'.$item->uri) }}" alt="">
											</a> -->
											<img class="thumb" src="{{ asset('storage'.$item->uri) }}" alt="">
											<div class="purchase">
												<!-- <a href="#">{{ $item->name.' '.$item->name_cn }}</a> -->
												<h6 id="product-{{ $item->id }}-name">
													{{ $item->name.' '.$item->name_cn }}
												</h6>
												<div class="form-row">
													<button type="button" class="product-qty product-qty-plus">+</button>
													<input class="product-qty-input product-category-{{ $item->category }}" id="product-qty-{{ $item->id }}" name="product-qty[{{ $item->id }}]"
														data-id="{{ $item->id }}" data-old="0" value="0" />
													<button type="button" class="product-qty product-qty-minus">-</button>
												</div>
												<div id="product-{{ $item->id }}-error"></div>
											</div>
										</div>
										<span class="price">
											HKD<label id="product-{{ $item->id }}-price" class="product-price-label" data-original-price="{{ $item->price }}">{{ $item->price }}</label>
										</span>
									</div>
								@endforeach
							</div>
							<div class="checkout">
								<div class="total">
									<span class="heading">小計 Subtotal :</span>
									<span class="monthly-price">
										每月 HKD<label class="monthly-price" id="monthly-price">0</label>
									</span>
									<span class="other-price">
										另加 HKD<label class="other-price" id="other-price">0</label>
									</span>
								</div>
							</div>
						</section>
						<!-- SECTION 3 － Account Information -->
						<h4></h4>
						<section>
							<div class="container">
								<input type="hidden" id="university" name="university" />
								<div class="row">
									<div class="col-md-6 col-sm-12 p-1">
										<label for="name">姓名 Name *</label>
										<input type="text" class="form-control" id="name" name="name" required>
									</div>
									<div class="col-md-6 col-sm-12 p-1">
										<label for="email">電郵地址 Email Address *</label>
										<input type="email" class="form-control" id="email" name="email">
									</div>
								</div>
								<div class="row">
									<div class="col-12 p-1">
										<label for="id_number">學生證號碼 Student ID No. *</label>
										<input type="text" class="form-control" id="id_number" name="id_number" required>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6 col-sm-12 p-1">
										<label for="mobile_phone_hk">手機號碼 (HK) Mobile No. (HK) *</label>
										<input type="text" placeholder="8 to 13 digits" class="form-control input-mobile-phone" id="mobile_phone_hk" name="mobile_phone_hk">
									</div>
									<div class="col-md-6 col-sm-12 p-1">
										<label for="mobile_phone_cn">手機號碼 (CN) Mobile No. (CN) *</label>
										<input type="text" placeholder="8 to 13 digits" class="form-control input-mobile-phone" id="mobile_phone_cn" name="mobile_phone_cn">
									</div>
								</div>
								<div class="row">
									<div class="col-12 p-1">
										<label for="address1">地址 Address *</label>
										<input type="text" id="address1" name="address1" class="form-control" placeholder="" style="margin-bottom: 20px" required>
										<input type="text" id="address2" name="address2" class="form-control" placeholder="">
									</div>
								</div>
								<div class="row">
									<div class="col-12 p-1">
										<label for="wechat">Wechat ID *</label>
										<input type="text" class="form-control" id="wechat" name="wechat" required>
									</div>
								</div>
							</div>
						</section>
						<!-- SECTION 4 - Checkin / Checkout Location 交收地點 -->
						<h4></h4>
						<section>
							<div class="container" id="checkinoutdata" data-isset="false">
								<div class="form-group">
									<div class="row radio">
										<label for="checkin_location">
											<input type="radio" id="checkin_location_type_default" name="checkin_location_type" value="default">指定取貨點(免費)：
										</label>
										<select class="form-control" id="checkin_location" name="checkin_location">
											<option value="">-- Please Select 請選擇 --</option>
										</select>
									</div>
									<div class="row radio">
										<label for="checkin_location_other">
											<input type="radio" id="checkin_location_type_other" name="checkin_location_type" value="other">其他取貨點(額外收費)*：
										</label>
										<input class="form-control" type="text" id="checkin_location_other" name="checkin_location_other">
									</div>
								</div>
								<div class="form-group">
									<div class="row radio">
										<label for="checkin_date">
											<input type="radio" id="checkin_date_type_default" name="checkin_date_type" value="default">指定取貨時間(免費)：
										</label>
										<select class="form-control checkinout_date" id="checkin_date" name="checkin_date">
											<option value="">-- Please Select 請選擇 --</option>
										</select>
									</div>
									<div class="row radio">
										<label for="checkin_date_other">
											<input type="radio" id="checkin_date_type_other" name="checkin_date_type" value="other">其他取貨時間(額外收費)*：
										</label>
									</div>
									<div class="row">
										<div class="col-md-8 p-1">
											<input class="form-control checkinout_date" type="date" id="checkin_date_other" name="checkin_date_other">
										</div>
										<div class="col-md-4 p-1">
											<select class="form-control" id="checkin_time_other" name="checkin_time_other">
												<option value="">AM / PM</option>
												<option value="AM">AM</option>
												<option value="PM">PM</option>
											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row radio">
										<label for="checkout_location">
											<input type="radio" id="checkout_location_type_default" name="checkout_location_type" value="default">指定收貨點(免費)：
										</label>
										<select class="form-control" id="checkout_location" name="checkout_location">
											<option value="">-- Please Select 請選擇 --</option>
										</select>
									</div>
									<div class="row radio">
										<label for="checkout_location_other">
											<input type="radio" id="checkout_location_type_other" name="checkout_location_type" value="other">其他收貨點(額外收費)*：
										</label>
										<input class="form-control" type="text" id="checkout_location_other" name="checkout_location_other">
									</div>
								</div>
								<div class="form-group">
									<div class="row radio">
										<label for="checkout_date">
											<input type="radio" id="checkout_date_type_default" name="checkout_date_type" value="default">指定收貨時間(免費)：
										</label>
										<select class="form-control checkinout_date" id="checkout_date" name="checkout_date">
											<option value="">-- Please Select 請選擇 --</option>
										</select>
									</div>
									<div class="row radio">
										<label for="checkout_date_other">
											<input type="radio" id="checkout_date_type_other" name="checkout_date_type" value="other">其他收貨時間(額外收費)*：
										</label>
									</div>
									<div class="row">
										<div class="col-md-8 p-1">
											<input class="form-control checkinout_date" type="date" id="checkout_date_other" name="checkout_date_other">
										</div>
										<div class="col-md-4 p-1">
											<select class="form-control" id="checkout_time_other" name="checkout_time_other">
												<option value="">AM / PM</option>
												<option value="AM">AM</option>
												<option value="PM">PM</option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</section>
						<!-- SECTION 5 -->
						<h4></h4>
						<section>
							<div class="order-preview">
								<div class="order-content">
									<label class="preview-title">Account Information 帳戶資訊</label>
									<div id="preview-account" class="container">
									</div>
									<hr/>
									<label class="preview-title">Your Order 你的訂單</label>
									<div id="preview-product" class="container">
									</div>
									<hr/>
									<label class="preview-title">Checkin / Checkout Detail 交收詳情</label>
									<div id="preview-checkin" class="container">
									</div>
									<div id="preview-checkout" class="container">
									</div>
									<label id="delivery-service-fee-label" style="display:none;">{{ $deliveryService->price }}</label>
									<div id="delivery-service-remark" class="container" style="display:none;">
										<div class="col-12" style="font-size:10px;">{{ $deliveryService->description }}</div>
									</div>
									<hr/>
									<label class="preview-title">Summary 摘要</label>
									<div id="preview-summary" class="container">
									</div>
									<hr/>
								</div>
								<div class="order-input">
									<input type="hidden" id="product-total-fee" name="product-total-fee">
									<input type="hidden" id="storage-month" name="storage-month">
									<input type="hidden" id="delivery-service-fee" name="delivery-service-fee">
									<input type="hidden" id="total-fee" name="total-fee">
								</div>
							</div>
						</section>
						<!-- SECTION 6 -->
						<h4></h4>
						<section>
							<div class="checkbox-circle">
								@foreach($paymentTypes as $paymentType)
									<label class="{{ ($loop->first)?'active':'' }}">
										<input type="radio" name="order_payment_type" value="{{ $paymentType->id }}" {{ ($loop->first)?'checked':'' }}>{{ $paymentType->description }}
										<span class="checkmark"></span>
									</label>
								@endforeach
							</div>
							<div id="payment-info" class="payment-info">
								<label>付款資訊</label>
								<div class="form-group">
									<div id="card-element">
									</div>
									<div id="card-errors" class="error" role="alert"></div>
								</div>
							</div>
						</section>
					</div>
				</form>
			@endif
		</div>

		<script src="{{ asset('vendor/placeorder/js/jquery-3.3.1.min.js') }}"></script>

		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

		<script src="https://js.stripe.com/v3/"></script>

		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
		<!-- JQUERY STEP -->
		<script src="{{ asset('vendor/placeorder/js/jquery.steps.js') }}"></script>

		<script src="{{ asset('vendor/placeorder/js/jquery.metadata.js') }}"></script>

		<script src="{{ asset('vendor/placeorder/js/jquery.validate.min.js') }}"></script>
		<script src="{{ asset('vendor/placeorder/js/jquery.additional-methods.min.js') }}"></script>
		<!-- <script src="{{ asset('vendor/placeorder/js/main.js') }}"></script> -->
		<!-- Template created and distributed by Colorlib -->
		<script>
			var form = $("#placeorder");
			form.validate({
	      rules: {
					email: {
			      required: true,
			      email: true,
			      remote: {
			        url: "{{ route('ajax-email-validate') }}",
			        type: "post",
			        data: {
			          email: function() {
			            return $( "#email" ).val();
			          }
			        }
			      }
			    },
					mobile_phone_hk: {
						required: function(element){
		            return $("#mobile_phone_cn").val().length == 0;
		        },
						pattern: /^\+?\d{8,13}/
					},
					mobile_phone_cn: {
						required: function(element){
		            return $("#mobile_phone_hk").val().length == 0;
		        },
						pattern: /^\+?\d{8,13}/
					}
				},
	      messages: {
					email: {
						required: "Please input Email Address",
						email: "Please input a valid Email Address",
						remote: "This email has been taken already",
					},
					mobile_phone_hk: {
						required: "Please input either Mobile Phone (HK) or Mobile Phone (CN)",
						pattern: "Phone No. should be 8 to 13 digits"
					},
					mobile_phone_cn: {
						required: "Please input either Mobile Phone (HK) or Mobile Phone (CN)",
						pattern: "Phone No. should be 8 to 13 digits"
					}
				},
	      errorPlacement: function(error, element) {
					console.log('error :'+error.toString());
          if (element.attr('class').includes('product-category-box')) {
						error.appendTo($('#product-'+element.data('id')+'-error'));
          }
          else {
            error.insertAfter(element);
          }
	      }
			});

			$(function(){
				// Set All Date must be select larger than today
				$('[type="date"]').prop('min', function(){
					return new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toJSON().split('T')[0];
		    });

				$.validator.addMethod("validtimerange", function(value, element) {
					var checkindateselect = $('#checkin_date option:selected');
					var checkinotherdate = $('#checkin_date_other').val();
					var checkoutdateselect = $('#checkout_date option:selected');
					var checkoutotherdate = $('#checkout_date_other').val();

					var checkin;
					var checkout;
					// console.log('checkindateselectval : '+checkindateselect.val());
					// console.log('checkoutdateselect : '+checkindateselect.val());

					if(!$('#checkin_date').is(':disabled') && checkindateselect.val() != ''){
						checkin = new Date(checkindateselect.text().split(" ")[0]);
						// console.log('checkindate : '+checkindateselect.text().split(" ")[0]);
					}else{
						checkin = new Date(checkinotherdate);
						// console.log('checkindate : '+checkinotherdate);
					}
					if(!$('#checkout_date').is(':disabled') && checkoutdateselect.val() != ''){
						checkout = new Date(checkoutdateselect.text().split(" ")[0]);
						// console.log('checkoutdate : '+checkoutdateselect.text().split(" ")[0]);
					}else{
						checkout = new Date(checkoutotherdate);
						// console.log('checkoutdate : '+checkoutotherdate);
					}
					// console.log('checkin Time : '+checkin.getTime());
					// console.log('checkout Time : '+checkout.getTime());

					if(isNaN(checkout.getTime()) || isNaN(checkin.getTime()))
						return true;
					return checkout.getTime() > checkin.getTime();

				}, "Checkout date must later then checkin date.");

				$.validator.addMethod("havebox", function(value, element) {
					var total = 0;

					$(".product-category-box").each(function() {
						total += parseInt($(this).val());
					});
					// console.log(total);
					return total > 0;

				}, "Please select at least one box.");

				$.validator.addClassRules("product-category-box", {
					havebox:true
				});
				$.validator.addClassRules("checkinout_date", {
					validtimerange:true
				});
				// $("#wizard").steps({
				form.children("div").steps({
							headerTag: "h4",
							bodyTag: "section",
							transitionEffect: "fade",
							enableAllSteps: false,
							transitionEffectSpeed: 500,
							onStepChanging: function (event, currentIndex, newIndex) {
									var valid = false;
									// Only Process when go next step
									if ( currentIndex < newIndex ) {
										form.validate().settings.ignore = ":disabled,:hidden";
										valid = form.valid();
									} else {
										valid = true;
									}
									// Only Process when go prev step
									if ( currentIndex > newIndex ) {

									}
									// valid = true;
									if(valid)
									{
										if ( newIndex === 0 ) {
												resetPrice();
												resetCheckinoutData();
										}
										if ( newIndex === 1 ) {
												$('.steps ul').addClass('step-2');

												if (newIndex > currentIndex) {
													$.post('/ajax/coupon-code/validate', { couponCode: $('#couponCode').val() })
											      .done(function (data){
											        // success data
											        // console.log(data);
															if (data.university > 0) {
																$('#university').val(data.university);

																if (data.universityPrices.length > 0)
																{
																	for(var i = 0; i < data.universityPrices.length; i++){
																		// console.log(data.universityPrices[i]);
																		var item_price = data.universityPrices[i];
																		$('#product-'+item_price.item_id+'-price').html(item_price.price);
																	}
																}
															}
											      })
											      .fail(function() {});
												}

										} else {
												$('.steps ul').removeClass('step-2');
										}
										if ( newIndex === 2 ) {
												$('.steps ul').addClass('step-3');
										} else {
												$('.steps ul').removeClass('step-3');
										}
										if ( newIndex === 3 ) {
												$('.steps ul').addClass('step-4');

												if (!$('#checkinoutdata').data('isset')) {
													if ($('#university').val() !== ""){
														$.post('/ajax/university/info', { university: $('#university').val() })
															.done(function (data){
																// success data
																// console.log(data);
																if(data.universityLocation.length > 0){
																	for(var i = 0; i < data.universityLocation.length; i++){
																		var location = data.universityLocation[i];

																		$('#checkin_location').append(new Option(location.location, location.id));
																		$('#checkout_location').append(new Option(location.location, location.id));

																		initLocation(true);
																	}
																}else{
																	initLocation(false);
																}

																if(data.universityDatetime.length > 0){
																	for(var i = 0; i < data.universityDatetime.length; i++){
																		var datetime = data.universityDatetime[i];

																		$('#checkin_date').append(new Option(datetime.date+' '+datetime.period, datetime.id));
																		$('#checkout_date').append(new Option(datetime.date+' '+datetime.period, datetime.id));
																		//
																		initDate(true);
																	}
																}else{
																	initDate(false);
																}
																//
																$('#checkinoutdata').data('isset', true);
															})
															.fail(function() {});
													}else{
														initLocation(false);
														initDate(false);
													}
												}

										} else {
												$('.steps ul').removeClass('step-4');
										}
										if ( newIndex === 4 ) {
												$('.steps ul').addClass('step-5');

												if (newIndex > currentIndex) {
													resetOrderPreview();
													createOrderPreview();
												}
										} else {
												$('.steps ul').removeClass('step-5');
										}

										if ( newIndex === 5 ) {
												$('.steps ul').addClass('step-6');
												$('.actions ul').addClass('step-last');
										} else {
												$('.steps ul').removeClass('step-6');
												$('.actions ul').removeClass('step-last');
										}
									}

									return valid;
							},
							onFinishing: function (event, currentIndex){
					        form.validate().settings.ignore = ":disabled";
					        return form.valid();
					    },
							onFinished: function (event, currentIndex){
									form.submit();
									// console.log($('input[type=radio][name=order_payment_type]:checked').val());
					    },
							labels: {
									finish: "Place Order",
									next: "Next",
									previous: "Previous"
							}
					});
					// Custom Steps Jquery Steps
					$('.wizard > .steps li a').click(function(){
						$(this).parent().addClass('checked');
						$(this).parent().prevAll().addClass('checked');
						$(this).parent().nextAll().removeClass('checked');
					});
					// Custom Button Jquery Steps
					$('.forward').click(function(){
						$("#wizard").steps('next');
					})
					$('.backward').click(function(){
							$("#wizard").steps('previous');
					})
					// Checkbox
					$('.checkbox-circle label').click(function(){
							$('.checkbox-circle label').removeClass('active');
							$(this).addClass('active');
					})

					$(".product-qty-plus").click(function(){
							// console.log('target input id : '+$(this).next('input').attr('id'));
							var qtyInput = $(this).next('input');
							var currVal = (qtyInput.val() === '' || isNaN(qtyInput.val()))? 0:parseFloat(qtyInput.val());

							if(currVal < 99){
								$(this).next('input').val(currVal+1);
								refreshPrice($(this).next('input'));
							}
					});

					$(".product-qty-minus").click(function(){
							// console.log('target input id : '+$(this).prev('input').attr('id'));
							var qtyInput = $(this).prev('input');
							var currVal = (qtyInput.val() === '' || isNaN(qtyInput.val()))? 0:parseFloat(qtyInput.val());

							if(currVal > 0){
								$(this).prev('input').val(currVal-1);
								refreshPrice($(this).prev('input'));
							}
					});

					$(".product-qty-input").on('change',function(e){
						var input = $(this);
						var prevVal = input.data('old');
					  var currVal = input.val();

						if(currVal < 0 || currVal > 99){
							// Invalid Value! Get old value
							input.val(prevVal);
						}else{
							input.data('old', currVal);
							refreshPrice();
						}

					});

					$('input[type=radio][name=checkin_location_type]').change(function() {
							// console.log($(this).val());
							if ($(this).val() == 'default') {
									$('#checkin_location').prop('disabled', false);
									$('#checkin_location').prop('required', true);
		            	$('#checkin_location_other').prop('disabled', true);
		            	$('#checkin_location_other').prop('required', false);
			        }
			        else if ($(this).val() == 'other') {
			        		$('#checkin_location').prop('disabled', true);
									$('#checkin_location').prop('required', false);
									$('#checkin_location_other').prop('disabled', false);
		            	$('#checkin_location_other').prop('required', true);
			        }
			    });

					$('input[type=radio][name=checkout_location_type]').change(function() {
							// console.log($(this).val());
		        	if ($(this).val() == 'default') {
			        		$('#checkout_location').prop('disabled', false);
									$('#checkout_location').prop('required', true);
			        		$('#checkout_location_other').prop('disabled', true);
		            	$('#checkout_location_other').prop('required', false);
			        }
			        else if ($(this).val() == 'other') {
			        		$('#checkout_location').prop('disabled', true);
									$('#checkout_location').prop('required', false);
									$('#checkout_location_other').prop('disabled', false);
		            	$('#checkout_location_other').prop('required', true);
			        }
			    });

					$('input[type=radio][name=checkin_date_type]').change(function() {
							// console.log($(this).val());
							if ($(this).val() == 'default') {
									$('#checkin_date').prop('disabled', false);
									$('#checkin_date').prop('required', true);
		            	$('#checkin_date_other').prop('disabled', true);
		            	$('#checkin_date_other').prop('required', false);
									$('#checkin_time_other').prop('disabled', true);
		            	$('#checkin_time_other').prop('required', false);
			        }
			        else if ($(this).val() == 'other') {
			        		$('#checkin_date').prop('disabled', true);
									$('#checkin_date').prop('required', false);
									$('#checkin_date_other').prop('disabled', false);
		            	$('#checkin_date_other').prop('required', true);
									$('#checkin_time_other').prop('disabled', false);
		            	$('#checkin_time_other').prop('required', true);
			        }
			    });

					$('input[type=radio][name=checkout_date_type]').change(function() {
							// console.log($(this).val());
		        	if ($(this).val() == 'default') {
			        		$('#checkout_date').prop('disabled', false);
									$('#checkout_date').prop('required', true);
			        		$('#checkout_date_other').prop('disabled', true);
		            	$('#checkout_date_other').prop('required', false);
			        		$('#checkout_time_other').prop('disabled', true);
		            	$('#checkout_time_other').prop('required', false);
			        }
			        else if ($(this).val() == 'other') {
			        		$('#checkout_date').prop('disabled', true);
									$('#checkout_date').prop('required', false);
									$('#checkout_date_other').prop('disabled', false);
		            	$('#checkout_date_other').prop('required', true);
									$('#checkout_time_other').prop('disabled', false);
		            	$('#checkout_time_other').prop('required', true);
			        }
			    });
					//
					$('input[type=radio][name=order_payment_type]').change(function() {
						if ($(this).val() == '3') {
							$('#payment-info').show();
						}else{
							$('#payment-info').hide();
						}
					});
					// Fix Browser which not support HTML input date
					if($('[type="date"]').prop('type') != 'date') {
						$('[type="date"]').datepicker({ dateFormat: 'yy-mm-dd' });
					}
			})

			function togglePrevious(enable) { toggleButton("previous", enable); }
			function toggleNext    (enable) { toggleButton("next",     enable); }
			function toggleFinish  (enable) { toggleButton("finish",   enable); }
			function toggleButton(buttonId, enable)
			{
			    if (enable)
			    {
			        // Enable disabled button
			        var button = $("#wizard").find('a[href="#' + buttonId + '-disabled"]');
			            button.attr("href", '#' + buttonId);
			            button.parent().removeClass();
			    }
			    else
			    {
			        // Disable enabled button
			        var button = $("#wizard").find('a[href="#' + buttonId + '"]');
			            button.attr("href", '#' + buttonId + '-disabled');
			            button.parent().addClass("disabled");
			    }
			}

			function initLocation( haveDefaultLoc ){
				if( haveDefaultLoc ) {
					$('#checkin_location_type_default').prop('checked', true).change();
					$('#checkout_location_type_default').prop('checked', true).change();
				}else{
					$('#checkin_location_type_default').prop('disabled', true);
					$('#checkin_location_type_other').prop('checked', true).change();

					$('#checkout_location_type_default').prop('disabled', true);
					$('#checkout_location_type_other').prop('checked', true).change();
				}
			}

			function initDate( haveDefaultDate ){
				if( haveDefaultDate ) {
					$('#checkin_date_type_default').prop('checked', true).change();
					$('#checkout_date_type_default').prop('checked', true).change();
				}else{
					$('#checkin_date_type_default').prop('disabled', true);
					$('#checkin_date_type_other').prop('checked', true).change();

					$('#checkout_date_type_default').prop('disabled', true);
					$('#checkout_date_type_other').prop('checked', true).change();
				}
			}

			function resetCheckinoutData(){
				$('#university').val('');
				$('#checkinoutdata').data('isset', false);
				resetLocation();
				resetDate();
			}

			function resetLocation(){
				// Clear Option
				$('#checkin_location option').each(function () {
				    if ($(this).val() !== '') {
				        $(this).remove();
				    }
				});
				$('#checkout_location option').each(function () {
				    if ($(this).val() !== '') {
				        $(this).remove();
				    }
				});
			}

			function resetDate(){
				// Clear Option
				$('#checkin_date option').each(function () {
				    if ($(this).val() !== '') {
				        $(this).remove();
				    }
				});

				$('#checkout_date option').each(function () {
				    if ($(this).val() !== '') {
				        $(this).remove();
				    }
				});
			}

			function resetPrice(){
				// Select Product Qty = 0
				$(".product-qty-input").each(function() {
					$(this).val(0);
				});
				//
				$(".product-price-label").each(function() {
					$(this).html($(this).data('original-price'));
				});
				//
				$('#monthly-price').html(0);
				$('#other-price').html(0);
			}

			function refreshPrice(){
				var monthlyPrice = 0;
				var otherPrice = 0;
				var itemQty = 0;
				var itemPrice = 0;

				$(".product-item").each(function() {
						var itemId = $(this).data('id');
						var itemCategory = $(this).data('category');

						itemQty = $('#product-qty-'+itemId).val();
						itemPrice = $('#product-'+itemId+'-price').html();
						// console.log('select product sub-total : '+(itemQty * itemPrice));
						if('box' === itemCategory){
							monthlyPrice += itemQty * itemPrice;
						}else{
							otherPrice += itemQty * itemPrice;
						}
				});

				// console.log('Total : '+total);
				$('#monthly-price').html(monthlyPrice);
				$('#other-price').html(otherPrice);
			}

			function resetOrderPreview(){
				$('#preview-product').html('');
				$('#preview-account').html('');
				$('#preview-checkin').html('');
				$('#preview-checkout').html('');
				$('#preview-summary').html('');
				$('#delivery-service-remark').hide();

				$('#product-total-fee').val(0);
				$('#delivery-service-fee').val(0);
				$('#total-fee').val(0);
			}

			function createOrderPreview(){
				var previewProductHtml = '<div class="row">';
				previewProductHtml += '<div class="col-md-8 col-xs-12">產品</div>';
				previewProductHtml += '<div class="col-md-2 col-xs-6">數量</div>';
				previewProductHtml += '<div class="col-md-2 col-xs-6">單價</div>';
				previewProductHtml += '</div>';

				var boxCount = 0;
				$(".product-item").each(function() {
						var itemId = $(this).data('id');
						var itemCategory = $(this).data('category');

						var itemNameLabel = $('#product-'+itemId+'-name').text();
						var itemQtyInput = $('#product-qty-'+itemId).val();
						var itemPriceLabel = $('#product-'+itemId+'-price').text();

						var itemQty = parseFloat(itemQtyInput);

						if(itemQty > 0)
						{
							if(itemCategory === 'box')
								boxCount += itemQty;

							previewProductHtml += '<div class="row">';
							previewProductHtml += '<div class="col-md-8 col-xs-12"><label id="preview-product-'+itemId+'-name">'+itemNameLabel+'</label></div>';
							previewProductHtml += '<div class="col-md-2 col-xs-6 text-right"><label id="preview-product-'+itemId+'-qty">'+itemQtyInput+'</label></div>';
							previewProductHtml += '<div class="col-md-2 col-xs-6 text-right"><label id="preview-product-'+itemId+'-price">HKD'+itemPriceLabel+'</label></div>';
							previewProductHtml += '</div>';
						}
				});
				// var subTotal = parseFloat($('#total-price').text());
				var monthlyPrice = parseFloat($('#monthly-price').text());
				var otherPrice = parseFloat($('#other-price').text());

				// console.log('Box Count : '+boxCount);
				var deliveryServiceFee = parseFloat($('#delivery-service-fee-label').text());
				var deliveryServiceTotal = (boxCount > 4)?(deliveryServiceFee * boxCount + deliveryServiceFee):(deliveryServiceFee * 4 + deliveryServiceFee);
				// console.log('Delivery Service Total : '+deliveryServiceTotal);

				previewProductHtml += '<div class="row">';
				previewProductHtml += '<div class="col-md-6 col-xs-12">小計 Subtotal</div>';
				previewProductHtml += '<div class="col-md-6 col-xs-12 text-right"><label id="preview-product-monthly-price">每月 HKD'+monthlyPrice+'</label></div>';
				previewProductHtml += '</div>';

				previewProductHtml += '<div class="row">';
				previewProductHtml += '<div class="col-md-6 col-xs-12"></div>';
				previewProductHtml += '<div class="col-md-6 col-xs-12 text-right"><label id="preview-product-other-price">另加 HKD'+otherPrice+'</label></div>';
				previewProductHtml += '</div>';

				$('#preview-product').append(previewProductHtml);

				var previewAccountHtml = '';
				var accountDataArray = ['name','email','id_number','mobile_phone_hk','mobile_phone_cn','address1','address2','wechat'];
				accountDataArray.forEach(buildAccountHtml);

				function buildAccountHtml(inputKey) {
					var label = $('label[for='+inputKey+']').text();
					var value = $('#'+inputKey).val();

					previewAccountHtml += '<div class="row"><div class="col-md-8 col-xs-12">'+label+'</div><div class="col-md-4 col-xs-12">'+value+'</div></div>';
				}

				$('#preview-account').append(previewAccountHtml);

				var checkinoutDataArray = ['location','location_other','date',['date_other','time_other']];

				var previewCheckinHtml = '';
				var previewCheckoutHtml = '';
				var haveOtherLocOrDate = false;
				//
				var checkindate = '';
				var checkoutdate = '';

				checkinoutDataArray.forEach(buildCheckinoutHtml);

				function buildCheckinoutHtml(inputKey) {
					previewCheckinHtml += buildCheckinoutRowHtml('checkin', inputKey);
					previewCheckoutHtml += buildCheckinoutRowHtml('checkout', inputKey);
				}

				function buildCheckinoutRowHtml(prefix, inputKey){
					// console.log(prefix+':'+inputKey);
					if(Array.isArray(inputKey)){
						var label = $('label[for='+prefix+'_'+inputKey[0]+']').text();
						var value = '';
						for(var i = 0; i < inputKey.length; i++){
							var disable = $('#'+prefix+'_'+inputKey[i]).is(':disabled');
							if(disable)
								continue;
							if(value != '')
								value += ' ';
							value += $('#'+prefix+'_'+inputKey[i]).val();
							if(inputKey[i].includes("other") && value != '')
								haveOtherLocOrDate = true;
							if('checkin' == prefix && inputKey[i].includes("date") && value != '')
								checkindate = value;
							if('checkout' == prefix && inputKey[i].includes("date") && value != '')
								checkoutdate = value;
						}
						// console.log(label+' '+value);
						if(value === '')
							return '';
						return '<div class="row"><div class="col-md-4 col-xs-12">'+label+'</div><div class="col-md-8 col-xs-12">'+value+'</div></div>';
					}else{
						var key = prefix+'_'+inputKey;
						var label = $('label[for='+key+']').text();
						var value = $('#'+key).val();
						var disable = $('#'+key).is(':disabled');
						if(disable)
							return '';
						if($('#'+key).is("select"))
						{
							if(value != '')
								value = $('#'+key+' option:selected').text();
						}
						// console.log(label+' '+value);
						if(value === '')
							return '';
						if(inputKey.includes("other"))
							haveOtherLocOrDate = true;
						if('checkin' == prefix && inputKey.includes("date") && value != '')
							checkindate = value.split(" ")[0];
						if('checkout' == prefix && inputKey.includes("date") && value != '')
							checkoutdate = value.split(" ")[0];
						return '<div class="row"><div class="col-md-5 col-xs-12">'+label+'</div><div class="col-md-7 col-xs-12">'+value+'</div></div>';
					}
				}

				var storageMonth = calcDateDiffByMonth(checkindate,checkoutdate);

				// console.log(checkindate+':'+checkoutdate+':'+storageMonth);

				$('#preview-checkin').append(previewCheckinHtml);
				$('#preview-checkout').append(previewCheckoutHtml);

				if(haveOtherLocOrDate){
					$('#delivery-service-remark').show();
				}

				var previewSummaryHtml = '';
				var total = 0;
				var subTotal = (monthlyPrice * storageMonth) + otherPrice;

				previewSummaryHtml += '<div class="row">';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12">產品收費 Product Fee</div>';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12 text-right"><label>每月 HKD'+monthlyPrice+'</label></div>';
				previewSummaryHtml += '</div>';

				previewSummaryHtml += '<div class="row">';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12"></div>';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12 text-right"><label>儲存期(月):'+storageMonth+'</label></div>';
				previewSummaryHtml += '</div>';

				previewSummaryHtml += '<div class="row">';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12"></div>';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12 text-right"><label>另加 HKD'+otherPrice+'</label></div>';
				previewSummaryHtml += '</div>';

				previewSummaryHtml += '<div class="row">';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12"></div>';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12 text-right"><label>HKD:'+subTotal+'</label></div>';
				previewSummaryHtml += '</div>';

				total += subTotal;

				if(haveOtherLocOrDate){
					previewSummaryHtml += '<div class="row">';
					previewSummaryHtml += '<div class="col-md-6 col-xs-12">外送服務 額外收費 Delivery Service Extra Fee</div>';
					previewSummaryHtml += '<div class="col-md-6 col-xs-12 text-right"><label>HKD'+deliveryServiceTotal+'</label></div>';
					previewSummaryHtml += '</div>';

					total += deliveryServiceTotal;
				}

				previewSummaryHtml += '<div class="row">';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12">總收費 Total Fee</div>';
				previewSummaryHtml += '<div class="col-md-6 col-xs-12 text-right"><label>HKD'+total+'</label></div>';
				previewSummaryHtml += '</div>';

				$('#product-total-fee').val(subTotal);
				$('#storage-month').val(storageMonth);
				$('#delivery-service-fee').val(deliveryServiceTotal);
				$('#total-fee').val(total);

				$('#preview-summary').append(previewSummaryHtml);
			}

			function calcDateDiffByMonth(d1Str, d2Str){
				var d1 = new Date(d1Str);
				var d2 = new Date(d2Str);

				var yearDiff = d2.getFullYear() - d1.getFullYear();
				var monthDiff = d2.getMonth() - d1.getMonth();
				var dayDiff = d2.getDate() - d1.getDate();

				var result = (yearDiff * 12) + monthDiff + ((dayDiff > 0)?1:0);
				// console.log(yearDiff+':'+monthDiff+':'+dayDiff+':'+result);
				return result;
			}
		</script>

		<script>
			$(function(){
				// Create a Stripe client.
				// var stripe = Stripe('{{ env("STRIPE_TEST_KEY") }}');
				var stripe = Stripe('{{ env("STRIPE_KEY") }}');
				// Create an instance of Elements.
				var elements = stripe.elements();
				// Custom styling can be passed to options when creating an Element.
				// (Note that this demo uses a wider set of styles than the guide below.)
				var style = {
					base: {
						color: '#32325d',
						fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
						fontSmoothing: 'antialiased',
						fontSize: '16px',
						'::placeholder': {
								color: '#aab7c4'
						}
					},
					invalid: {
						color: '#fa755a',
						iconColor: '#fa755a'
					}
				};
				// Create an instance of the card Element.
				var card = elements.create('card', {style: style, hidePostalCode: true});
				// Add an instance of the card Element into the `card-element` <div>.
				card.mount('#card-element');
				// Handle real-time validation errors from the card Element.
				card.addEventListener('change', function(event) {
					var displayError = document.getElementById('card-errors');
					if (event.error) {
						displayError.textContent = event.error.message;
					} else {
						displayError.textContent = '';
					}
				});
				// Handle form submission.
				var submitHandler = function( event ) {
					toggleFinish(false);

					if($('input[type=radio][name=order_payment_type]:checked').val() == '3')
					{
						event.preventDefault();
						stripe.createToken(card).then(function(result) {
							if (result.error) {
								// Inform the user if there was an error.
								var errorElement = document.getElementById('card-errors');
								errorElement.textContent = result.error.message;

								toggleFinish(true);
							} else {
								// Send the token to your server.
								stripeTokenHandler(result.token);
							}
						});
					}
				}

				$("#placeorder").bind("submit", submitHandler);
			})
			// Submit the form with the token ID.
			function stripeTokenHandler(token) {
				console.log('on stripeTokenHandler');
				// Insert the token ID into the form so it gets submitted to the server
				var paymentform = document.getElementById('placeorder');
				var hiddenInput = document.createElement('input');
				hiddenInput.setAttribute('type', 'hidden');
				hiddenInput.setAttribute('name', 'stripeToken');
				hiddenInput.setAttribute('value', token.id);
				paymentform.appendChild(hiddenInput);
				// Submit the form
				paymentform.submit();
			}
		</script>
		@component('components.wechatpay-success-auto-redirect')
	  @endcomponent
</body>
</html>
