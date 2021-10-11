<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all (Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit',6);
        $status = $request->input('status');
        
        
        if($id){
            $trx = Transaction::with(['items.product'])->find($id);

            if($trx){
                return ResponseFormatter::success(
                    $trx, 
                    'Transactions berhasil diambil'
                );
            }else{
                return ResponseFormatter::error(
                    null,'Data Transactions tidak ada',404
                );
            }

        }

        $trx = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);

        if ($status){
            $trx->where('status', $status);
        }

        return ResponseFormatter::success($trx->paginate($limit),'data list transaksi berhasil diambil');
    }

    public function checkout(Request $request){
        $request->validate([
            'items' => 'required|array',
            'items.*.id' =>'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING, SUCCESS, CANCELLED, FAILED, SHIPPING, SHIPPED'
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status,
            'payment' => $request->payment,
        ]);

        //$transaction_id = Transaction::latest('id')->first();
        foreach($request->items as $product){
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        return ResponseFormatter::success($transaction->load('items.product'),'Data transaksi berhasil diinput');
    }
}
