<?php
 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 
use App\Http\Controllers\Controller;
use App\User;

use App\SomsClient;
use App\SomsUniversity;
use App\SomsOrder;
use App\SomsStoragePeriod;
use App\SomsPromotion;
use App\SomsPromotionItem;
use App\SomsItem;
use App\SomsOrderPayment;
use App\SomsPaymentStatus;
use App\SomsPaymentType;
use App\SomsOrderStatus;
use App\SomsStoragePeriodItem;
use App\SomsOrderItem;

use App\AdminUser;
use Illuminate\Support\Facades\Hash;

use Auth;
use Log;
use Mail;

use App\Mail\ExtendedPaymentInvoice;


 
class ApiAdminController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */

    public function authAdmin(Request $request)
    {
      $username = $request->get('username');
      $password = $request->get('password');

      $users = DB::select('select * from admin_users');
      $admin = AdminUser::where('username', $username)->first();

      if(!$admin) {
        $result['status'] = "error";
        $result['message'] = "Username is incorrect";
        return response()->json($result, 401);
      }

      if(Hash::check($password, $admin->password)) {
        $result['status'] = "success";
        $result['user'] = [
          'id' => $admin->id,
          'name' => $admin->name,
          'username' => $admin->username,
          'update' => $admin->updated_at,
        ];
        return response()->json($result);
      } else {
        $result['status'] = "error";
        $result['message'] = "Password is incorrect";
        return response()->json($result, 401);
      }
    } 

    public function index()
    {
        $universities = SomsUniversity::get();

        foreach ($universities as $university) {
          $university['ordersCount'] = $university -> ordersCount();
        }

        return response()->json($universities);
    }

    public function fetchProducts() 
    {
      $products = SomsItem::where('type','product')->where('category','box')->get();
      return response()->json($products);
    }

    public function fetchRef()
    {
      $paymentStatus = SomsPaymentStatus::get();
      $paymentMethods = SomsPaymentType::get();
      $orderStatus = SomsOrderStatus::get();
      return response()->json([
        'paymentStatus' => $paymentStatus,
        'paymentMethod' => $paymentMethods,
        'orderStatus' => $orderStatus,
      ]);
    }

    public function fetchClients(Request $request)
    {
      $filter = $request->get('filterData');
      $total = $request->get('total');
      $perPage = $request->get('perPage');
      $page = $request->get('page');
      $orderBy = $request->get('orderBy');
      $sort = $request->get('sort');

      $clients = SomsClient::distinct();

      if($orderBy && $sort) {
        $clients = $clients->orderBy($orderBy, $sort);
      } else {
        $clients = $clients->orderBy('id', 'desc');
      }

      if($filter['name'] != "") $clients = $clients->where('name', 'like', "%".$filter['name']."%");
      if($filter['email'] != "") $clients = $clients->where('email', 'like', "%".$filter['email']."%");
      if($filter['contact'] != "") $clients = $clients->where('contact', 'like', "%".$filter['contact']."%");
      if($filter['wechat'] != "") $clients = $clients->where('wechat', 'like', "%".$filter['wechat']."%");
      if($filter['student_id'] != "") $clients = $clients->where('student_id', 'like', "%".$filter['student_id']."%");
      
      $total = $clients->count();
      if($perPage * ($page - 1) > $total) $page = 1;
      $clients = $clients->offset($perPage * ($page - 1))->limit($perPage)->get();

      $result['data'] = $clients;
      $result['pagination']['total'] = $total;
      $result['pagination']['perPage'] = $perPage;
      $result['pagination']['page'] = $page;
      $result['pagination']['orderBy'] = $orderBy;
      $result['pagination']['sort'] = $sort;
      return response()->json($result);
    }

    public function deleteClient(Request $request)
    {
      $ids = $request->get('id');
      $num = 0;
      foreach ($ids as $id) {
        $num ++;
        $data[$num] = SomsClient::where('id', $id)->delete();
      };
      return response()->json([
        'status' => 'success',
        'data' => $data,
      ]);
    }

    public function editClient(Request $request)
    {
      $data = $request->get('data');
      $id = $request->get('id');

      if($id) {
        $client = SomsClient::where('id', $id)->first();
      } else {
        $client = new SomsClient;
        if($client['password']) {
          $client['password'] = Hash::make($data['password']);
          if($client['email'] != $data['email']) {
            $client['email'] = $data['email'];
          }
        }
      }
      if(!$client) {
        return response()->json([
          'status' => 'error'
        ], 402);
      }
      $client['name'] = $data['name'];
      $client['contact'] = $data['contact'];
      $client['address1'] = $data['address1'];
      $client['wechat'] = $data['wechat'];
      $client['student_id'] = $data['student_id'];
      if($data['universityId'] != "") {
        $client['university_id'] = $data['universityId'];
      } else {
        $client['university_id'] = null;
      }
      $client->save();
      
      return response()->json([
        'status' => 'success',
        'data' => $client,
      ]);
    }

    public function fetchPeriods(Request $request)
    {
      $filter = $request->get('filterData');
      $total = $request->get('total');
      $perPage = $request->get('perPage');
      $page = $request->get('page');
      $orderBy = $request->get('orderBy');
      $sort = $request->get('sort');

      $periods = SomsStoragePeriod::with('items');

      if($orderBy && $sort) {
        $periods = $periods->orderBy($orderBy, $sort);
      } else {
        $periods = $periods->orderBy('id', 'desc');
      }

      if($filter['name'] != "") $periods = $periods->where('name', 'like', "%".$filter['name']."%");
      if($filter['code'] != "") $periods = $periods->where('code', 'like', "%".$filter['code']."%");
      
      $total = $periods->count();
      if($perPage * ($page - 1) > $total) $page = 1;
      $periods = $periods->offset($perPage * ($page - 1))->limit($perPage)->get();

      $result['data'] = $periods;
      $result['pagination']['total'] = $total;
      $result['pagination']['perPage'] = $perPage;
      $result['pagination']['page'] = $page;
      $result['pagination']['orderBy'] = $orderBy;
      $result['pagination']['sort'] = $sort;
      return response()->json($result);
    }

    public function editPeriodItem(Request $request)
    {
      $items = $request->get('data');
      $id = $request->get('id');
      if(!$id) {
        return response()->json([
          'status' => 'error',
          'message' => 'no id'
        ], 402);
      }
      if($items['documentBox'] != "") {
        $periodItem = SomsStoragePeriodItem::where('storage_period_id', $id)->where('item_id', 2)->first();
        if(!$periodItem) {
          $periodItem = new SomsStoragePeriodItem;
        }
        $periodItem->item_id = 2;
        $periodItem->storage_period_id = $id;
        $periodItem->price = $items['documentBox'];
        $periodItem->save();
      } else {
        $periodItem = SomsStoragePeriodItem::where('storage_period_id', $id)->where('item_id', 2)->first();
        if($periodItem) {
          $periodItem->delete();
        }
      }

      if($items['overisizeBox'] != "") {
        $periodItem = SomsStoragePeriodItem::where('storage_period_id', $id)->where('item_id', 4)->first();
        if(!$periodItem) {
          $periodItem = new SomsStoragePeriodItem;
        }
        $periodItem->item_id = 4;
        $periodItem->storage_period_id = $id;
        $periodItem->price = $items['overisizeBox'];
        $periodItem->save();
      } else {
        $periodItem = SomsStoragePeriodItem::where('storage_period_id', $id)->where('item_id', 4)->first();
        if($periodItem) {
          $periodItem->delete();
        }
      }

      if($items['wardrobeBox'] != "") {
        $periodItem = SomsStoragePeriodItem::where('storage_period_id', $id)->where('item_id', 9)->first();
        if(!$periodItem) {
          $periodItem = new SomsStoragePeriodItem;
        }
        $periodItem->item_id = 9;
        $periodItem->storage_period_id = $id;
        $periodItem->price = $items['wardrobeBox'];
        $periodItem->save();
      } else {
        $periodItem = SomsStoragePeriodItem::where('storage_period_id', $id)->where('item_id', 9)->first();
        if($periodItem) {
          $periodItem->delete();
        }
      }

      if($items['packageBox'] != "") {
        $periodItem = SomsStoragePeriodItem::where('storage_period_id', $id)->where('item_id', 10)->first();
        if(!$periodItem) {
          $periodItem = new SomsStoragePeriodItem;
        }
        $periodItem->item_id = 10;
        $periodItem->storage_period_id = $id;
        $periodItem->price = $items['packageBox'];
        $periodItem->save();
      } else {
        $periodItem = SomsStoragePeriodItem::where('storage_period_id', $id)->where('item_id', 10)->first();
        if($periodItem) {
          $periodItem->delete();
        }
      }
    }

    public function deletePeriod(Request $request)
    {
      $ids = $request->get('id');
      $num = 0;
      foreach ($ids as $id) {
        $num ++;
        $data[$num] = SomsStoragePeriod::where('id', $id)->delete();
      };
      return response()->json([
        'status' => 'success',
        'data' => $data,
      ]);
    }

    public function editPeriod(Request $request)
    {
      $data = $request->get('data');
      $id = $request->get('id');

      if($id) {
        $period = SomsStoragePeriod::where('id', $id)->first();
      } else {
        $period = new SomsStoragePeriod;
      }
      if(!$period) {
        return response()->json([
          'status' => 'error'
        ], 402);
      }
      $period['name'] = $data['name'];
      $period['code'] = $data['code'];
      $period['min'] = $data['min'];
      $period['max'] = $data['max'];
      $period['period_type'] = 'month';
      $period->save();
      
      return response()->json([
        'status' => 'success',
        'data' => $period,
      ]);
    }

    public function fetchPromotions(Request $request)
    {
      $filter = $request->get('filterData');
      $total = $request->get('total');
      $perPage = $request->get('perPage');
      $page = $request->get('page');
      $orderBy = $request->get('orderBy');
      $sort = $request->get('sort');

      $promotions = SomsPromotion::with('items');

      if($orderBy && $sort) {
        $promotions = $promotions->orderBy($orderBy, $sort);
      } else {
        $promotions = $promotions->orderBy('id', 'desc');
      }

      if($filter['name'] != "") $promotions = $promotions->where('name', 'like', "%".$filter['name']."%");
      if($filter['code'] != "") $promotions = $promotions->where('code', 'like', "%".$filter['code']."%");
      if($filter['fromDateStart'] != "") $promotions = $promotions->where('effective_from', '>=', $filter['fromDateStart']);
      if($filter['fromDateEnd'] != "") $promotions = $promotions->where('effective_from', '<=', $filter['fromDateEnd']);
      if($filter['toDateStart'] != "") $promotions = $promotions->where('effective_to', '>=', $filter['toDateStart']);
      if($filter['toDateEnd'] != "") $promotions = $promotions->where('effective_to', '<=', $filter['toDateEnd']);

      
      $total = $promotions->count();
      if($perPage * ($page - 1) > $total) $page = 1;
      $promotions = $promotions->offset($perPage * ($page - 1))->limit($perPage)->get();

      $result['data'] = $promotions;
      $result['pagination']['total'] = $total;
      $result['pagination']['perPage'] = $perPage;
      $result['pagination']['page'] = $page;
      $result['pagination']['orderBy'] = $orderBy;
      $result['pagination']['sort'] = $sort;
      return response()->json($result);
    }

    public function editPromotion(Request $request)
    {
      $id = $request->get('id');
      $data = $request->get('data');

      if($id) {
        $promotion = SomsPromotion::where('id', $id)->first();
      } else {
        $promotion = new SomsPromotion;
      }
      if(!$promotion) {
        return response()->json([
          'status' => 'error'
        ], 402);
      }
      $promotion['name'] = $data['name'];
      $promotion['code'] = $data['code'];
      $promotion['effective_from'] = $data['effective_from'];
      $promotion['effective_to'] = $data['effective_to'];
      $promotion->save();
      
      return response()->json([
        'status' => 'success',
        'data' => $promotion,
      ]);
    }

    public function editPromotionItem(Request $request)
    {
      $items = $request->get('data');
      $id = $request->get('id');
      if(!$id) {
        return response()->json([
          'status' => 'error',
          'message' => 'no id'
        ], 402);
      }
      if($items['documentBox'] != "") {
        $periodItem = SomsPromotionItem::where('promotion_id', $id)->where('item_id', 2)->first();
        if(!$periodItem) {
          $periodItem = new SomsPromotionItem;
        }
        $periodItem->item_id = 2;
        $periodItem->promotion_id = $id;
        $periodItem->price = $items['documentBox'];
        $periodItem->save();
      } else {
        $periodItem = SomsPromotionItem::where('promotion_id', $id)->where('item_id', 2)->first();
        if($periodItem) {
          $periodItem->delete();
        }
      }

      if($items['overisizeBox'] != "") {
        $periodItem = SomsPromotionItem::where('promotion_id', $id)->where('item_id', 4)->first();
        if(!$periodItem) {
          $periodItem = new SomsPromotionItem;
        }
        $periodItem->item_id = 4;
        $periodItem->promotion_id = $id;
        $periodItem->price = $items['overisizeBox'];
        $periodItem->save();
      } else {
        $periodItem = SomsPromotionItem::where('promotion_id', $id)->where('item_id', 4)->first();
        if($periodItem) {
          $periodItem->delete();
        }
      }

      if($items['wardrobeBox'] != "") {
        $periodItem = SomsPromotionItem::where('promotion_id', $id)->where('item_id', 9)->first();
        if(!$periodItem) {
          $periodItem = new SomsPromotionItem;
        }
        $periodItem->item_id = 9;
        $periodItem->promotion_id = $id;
        $periodItem->price = $items['wardrobeBox'];
        $periodItem->save();
      } else {
        $periodItem = SomsPromotionItem::where('promotion_id', $id)->where('item_id', 9)->first();
        if($periodItem) {
          $periodItem->delete();
        }
      }

      if($items['packageBox'] != "") {
        $periodItem = SomsPromotionItem::where('promotion_id', $id)->where('item_id', 10)->first();
        if(!$periodItem) {
          $periodItem = new SomsPromotionItem;
        }
        $periodItem->item_id = 10;
        $periodItem->promotion_id = $id;
        $periodItem->price = $items['packageBox'];
        $periodItem->save();
      } else {
        $periodItem = SomsPromotionItem::where('promotion_id', $id)->where('item_id', 10)->first();
        if($periodItem) {
          $periodItem->delete();
        }
      }
    }

    public function deletePromotion(Request $request)
    {
      $ids = $request->get('id');
      $num = 0;
      foreach ($ids as $id) {
        $num ++;
        $data[$num] = SomsPromotion::where('id', $id)->delete();

        $promotionItems = SomsPromotionItem::where('promotion_id', $id)->delete();
      };
      return response()->json([
        'status' => 'success',
        'data' => $data,
      ]);
    }

    public function fetchPayments(Request $request)
    {
      $filter = $request->get('filterData');
      $orderId = $request->get('orderId');
      $total = $request->get('total');
      $perPage = $request->get('perPage');
      $page = $request->get('page');
      $orderBy = $request->get('orderBy');
      $sort = $request->get('sort');

      $payments = SomsOrderPayment::with('order', 'status');
      if($orderId) {
        $payments = $payments->where('order_id', $orderId);
      }

      if($orderBy && $sort) {
        $payments = $payments->orderBy($orderBy, $sort);
      } else {
        $payments = $payments->orderBy('id', 'desc');
      }

      if($filter['amount'] != "") $payments = $payments->where('amount', 'like', "%".$filter['amount']."%");
      if($filter['order_id'] != "") $payments = $payments->whereHas('order', function  ($q) use ($filter) {
        $q->where('code', 'like', "%".$filter['order_id']."%");
      });

      $total = $payments->count();
      if($perPage * ($page - 1) > $total) $page = 1;
      $payments = $payments->offset($perPage * ($page - 1))->limit($perPage);
      $payments = $payments->get();

      foreach($payments as $payment) {
        $payment->order->client;
      }

      $result['data'] = $payments;
      $result['pagination']['total'] = $total;
      $result['pagination']['perPage'] = $perPage;
      $result['pagination']['page'] = $page;
      $result['pagination']['orderBy'] = $orderBy;
      $result['pagination']['sort'] = $sort;
      return response()->json($result);
    }

    public function editPayment(Request $request)
    {

    }

    public function deletePayment(Request $request)
    {
      $ids = $request->get('id');
      $num = 0;
      foreach ($ids as $id) {
        $num ++;
        $data[$num] = SomsOrderPayment::where('id', $id)->delete();
      };
      return response()->json([
        'status' => 'success',
        'data' => $data,
      ]);
    }

    public function paymentCancelled(Request $request)
    {
      $id = $request->get('id');
      $payment = SomsOrderPayment::find($id);
      $payment->payment_status_id = SomsPaymentStatus::CANCELLED;
      $payment->paid_fee = $payment->amount;
      $payment->save();

      $currOrder = $payment->order;
      $currOrder->total_fee = $currOrder->total_fee - $payment->amount;
      $currOrder->payment_status_id = SomsPaymentStatus::PAID;
      $currOrder->save();

      return response()->json(['status' => 'success']);
    }

    public function paymentPaid(Request $request)
    {
      $id = $request->get('id');
      $payment = SomsOrderPayment::find($id);
      $payment->payment_status_id = SomsPaymentStatus::PAID;
      $payment->paid_fee = $payment->amount;
      $payment->save();

      $currOrder = $payment->order;
      $currOrder->paid_fee = $currOrder->paid_fee + $payment->amount;
      $currOrder->payment_status_id = SomsPaymentStatus::PAID;
      $currOrder->save();

      return response()->json(['status' => 'success']);
    }

    public function fetchOrders(Request $request)
    {
      $filter = $request->get('filterData');
      $uid = $request->get('uid');
      $total = $request->get('total');
      $perPage = $request->get('perPage');
      $page = $request->get('page');
      $orderBy = $request->get('orderBy');
      $sort = $request->get('sort');

      $orders = SomsOrder::with('client', 'items', 'status')->has('client');
      if($uid && $uid != "90") {
        $orders = $orders->whereHas('client', function ($q) use ($uid) {
          $q->where('university_id', $uid);
        });
      }

      if($orderBy && $sort) {
        $orders = $orders->orderBy($orderBy, $sort);
      } else {
        $orders = $orders->orderBy('code', 'desc');
      }

      if($filter['name'] != "") $orders = $orders->whereHas('client', function  ($q) use ($filter) {
        $q->where('name', 'like', "%".$filter['name']."%");
      });
      if($filter['email'] != "") $orders = $orders->whereHas('client', function  ($q) use ($filter) {
        $q->where('email', 'like', "%".$filter['email']."%");
      });
      if($filter['contact'] != "") $orders = $orders->whereHas('client', function  ($q) use ($filter) {
        $q->where('contact', 'like', "%".$filter['contact']."%");
      });
      if($filter['wechat'] != "") $orders = $orders->whereHas('client', function  ($q) use ($filter) {
        $q->where('wechat', 'like', "%".$filter['wechat']."%");
      });
      if($filter['student_id'] != "") $orders = $orders->whereHas('client', function  ($q) use ($filter) {
        $q->where('student_id', 'like', "%".$filter['student_id']."%");
      });

      if($filter['code'] != "") $orders = $orders->where('code', 'like', "%".$filter['code']."%");
      if($filter['emptyDateStart'] != "") $orders = $orders->whereDate('emptyout_date_other', '>', $filter['emptyDateStart']);
      if($filter['emptyDateEnd'] != "") $orders = $orders->whereDate('emptyout_date_other', '<', $filter['emptyDateEnd']);
      if($filter['checkinDateStart'] != "") $orders = $orders->whereDate('checkin_date_other', '>', $filter['checkinDateStart']);
      if($filter['checkinDateEnd'] != "") $orders = $orders->whereDate('checkin_date_other', '<', $filter['checkinDateEnd']);
      if($filter['checkoutDateStart'] != "") $orders = $orders->whereDate('checkout_date_other', '>', $filter['checkoutDateStart']);
      if($filter['checkoutDateEnd'] != "") $orders = $orders->whereDate('checkout_date_other', '<', $filter['checkoutDateEnd']);

     $filter_status = array();

      if($filter['status']['new'] == true) array_push($filter_status, 1);
      if($filter['status']['inProgress'] == true) array_push($filter_status, 4);
      if($filter['status']['emptyDelivery'] == true) array_push($filter_status, 8);
      if($filter['status']['schedCheckin'] == true) array_push($filter_status, 14);
      if($filter['status']['checkin'] == true) array_push($filter_status, 16);
      if($filter['status']['schedCheckout'] == true) array_push($filter_status, 20);
      if($filter['status']['checkout'] == true) array_push($filter_status, 24);
      if($filter['status']['schedEmptyReturn'] == true) array_push($filter_status, 25);
      if($filter['status']['completed'] == true) array_push($filter_status, 28);
      if($filter['status']['hold'] == true) array_push($filter_status, 30);
      if($filter['status']['cancelled'] == true) array_push($filter_status, 32);

      if(count($filter_status) > 0) $orders = $orders->whereIn('order_status_id', $filter_status);

      $total = $orders->count();
      if($perPage * ($page - 1) > $total) $page = 1;
      $orders = $orders->offset($perPage * ($page - 1))->limit($perPage);
      $orders = $orders->get();

      $result['data'] = $orders;
      $result['pagination']['total'] = $total;
      $result['pagination']['perPage'] = $perPage;
      $result['pagination']['page'] = $page;
      $result['pagination']['orderBy'] = $orderBy;
      $result['pagination']['sort'] = $sort;
      return response()->json($result);
    }

    public function editOrder(Request $request)
    {
      $id = $request->get('id');
      $data = $request->get('data');
      if($id == "") {
        $order = new SomsOrder;
      } else {
        $order = SomsOrder::where('id', $id)->first();
      }
      $order->emptyout_location_other = $data['emptyout_location_other'];
      $order->emptyout_date_other = $data['emptyout_date_other'];
      $order->emptyout_time_other = $data['emptyout_time_other'];
      $order->checkin_location_other = $data['checkin_location_other'];
      $order->checkin_date_other = $data['checkin_date_other'];
      $order->checkin_time_other = $data['checkin_time_other'];
      $order->checkout_location_other = $data['checkout_location_other'];
      $order->checkout_date_other = $data['checkout_date_other'];
      $order->checkout_time_other = $data['checkout_time_other'];
      $order->special_instruction = $data['special_instruction'];
      $order->paid_fee = $data['paid_fee'];
      $order->payment_type_id = $data['payment_type_id'];
      $order->payment_status_id = $data['payment_status_id'];
      $order->order_status_id = $data['order_status_id'];
      $order->storage_month = $data['storage_month'];
      $order->total_fee = $data['total_fee'];
      $order->save();

      $item_documentBox = SomsOrderItem::where('order_id', $order->id)->where('item_id', 2)->first();
      $item_oversizeBox = SomsOrderItem::where('order_id', $order->id)->where('item_id', 4)->first();
      $item_wardrobeBox = SomsOrderItem::where('order_id', $order->id)->where('item_id', 9)->first();
      $item_packageBox = SomsOrderItem::where('order_id', $order->id)->where('item_id', 10)->first();

      if($item_documentBox) {
        $item_documentBox->item_qty = $data['quantity']['documentBox'];
        $item_documentBox->item_price = $data['price']['documentBox'];
        $item_documentBox->save();
      } else {
        if($data['quantity']['documentBox'] != "0" && $data['price']['documentBox'] != "") {
          $item_documentBox = new SomsOrderItem;
          $item_documentBox->item_id = 2;
          $item_documentBox->order_id = $order->id;
          $item_documentBox->item_qty = $data['quantity']['documentBox'];
          $item_documentBox->item_price = $data['price']['documentBox'];
          $item_documentBox->save();
        }
      }
      if($item_oversizeBox) {
        $item_oversizeBox->item_qty = $data['quantity']['oversizeBox'];
        $item_oversizeBox->item_price = $data['price']['oversizeBox'];
        $item_oversizeBox->save();
      } else {
        if($data['quantity']['oversizeBox'] != "0" && $data['price']['oversizeBox'] != "") {
          $item_oversizeBox = new SomsOrderItem;
          $item_oversizeBox->item_id = 4;
          $item_oversizeBox->order_id = $order->id;
          $item_oversizeBox->item_qty = $data['quantity']['oversizeBox'];
          $item_oversizeBox->item_price = $data['price']['oversizeBox'];
          $item_oversizeBox->save();
        }
      }
      if($item_wardrobeBox) {
        $item_wardrobeBox->item_qty = $data['quantity']['wardrobeBox'];
        $item_wardrobeBox->item_price = $data['price']['wardrobeBox'];
        $item_wardrobeBox->save();
      } else {
        if($data['quantity']['wardrobeBox'] != "0" && $data['price']['wardrobeBox'] != "") {
          $item_wardrobeBox = new SomsOrderItem;
          $item_wardrobeBox->item_id = 9;
          $item_wardrobeBox->order_id = $order->id;
          $item_wardrobeBox->item_qty = $data['quantity']['wardrobeBox'];
          $item_wardrobeBox->item_price = $data['price']['wardrobeBox'];
          $item_wardrobeBox->save();
        }
      }
      if($item_packageBox) {
        $item_packageBox->item_qty = $data['quantity']['packageBox'];
        $item_packageBox->item_price = $data['price']['packageBox'];
        $item_packageBox->save();
      } else {
        if($data['quantity']['packageBox'] != "0" && $data['price']['packageBox'] != "") {
          $item_packageBox = new SomsOrderItem;
          $item_packageBox->item_id = 10;
          $item_packageBox->order_id = $order->id;
          $item_packageBox->item_qty = $data['quantity']['packageBox'];
          $item_packageBox->item_price = $data['price']['packageBox'];
          $item_packageBox->save();
        }
      }
      return response()->json(['status' => 'success']);
    }

    public function deleteOrder(Request $request)
    {
      $ids = $request->get('id');
      $num = 0;
      foreach ($ids as $id) {
        $num ++;
        $data[$num] = SomsOrder::where('id', $id)->delete();
      };
      return response()->json([
        'status' => 'success',
        'data' => $data,
      ]);
    }

    public function sendInvoice(Request $request)
    {
      $id = $request->get('id');
      $order = SomsOrder::find($id);
      $payment = $order->incompletePayment();

      try{
        Mail::to($order->client->email)->cc( env('MAIL_TO_ADDRESS') )->send(new ExtendedPaymentInvoice($order));
        Log::debug('Payment Invoice successfully send with email : '.$order->client->email.' payment id: '.$payment->id);
        
        return response()->json(['status' => 'success']);
      }
      catch(\Exception $e){
        Log::error('Payment Invoice cannot send with email : '.$order->client->email.' order code : '.$order->code);
        
        return response()->json(['status' => 'error'], 402);
      }
    }
}