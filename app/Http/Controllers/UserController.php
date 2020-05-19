<?php

namespace App\Http\Controllers;

use App\Invitation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function users(Request $request) {

        $users = User::where('id','!=',Auth::id())->withCount(['bets as bets_won' => function($query) {
            $query->whereResult(0)->select(\DB::raw('count(*)'));
        }])->when($request->search,function ($query) use ($request){
            $query->whereRaw("concat_ws(' ',name,email,username) 
like '%$request->search%'");

        })->where(function ($query){

            $query->whereDoesntHave('invitee')->orWhereHas('invitee',function ($query) {

                $query->whereNull('result');
            });
        })->select(['id','name','email'])->get();

        $users = collect($users->sortByDesc('bets_won'))->except(['id']);

        $i = 0;
        foreach ($users as $user) {

            $user->rank = ++$i;
        }

        return response()->json(['status' => true,'users' => $users->values()]);
    }

    public function addMoney(Request $request) {

        $user = Auth::user();
        $user->increment('money',$request->money);

        return response()->json(['status' => true,'balance' => $user->money]);
    }

    public function invite(User $user,Request $request) {

        $auth_user = Auth::user();
        $open_bet = $auth_user->bets()->whereNull('result')->sum('bet');
        $available_money = $auth_user->money - $open_bet;


        if ($request->bet > $available_money) {
            return response()->json(['status' => false,'message' => 'Not enough money to open bet']);
        }

        $bet = $auth_user->bets()->create([

            'bet' => $request->bet,
            'invitee_id' => $user->id,
            'flip' => rand(0,1)
        ]);

        return response()->json(['status' => true,'message' => 'Bet created']);
    }

    public function acceptBet(Invitation $invite) {

        $auth_user = Auth::user();
        $inviter = $invite->inviter;
        $open_bet = $auth_user->bets()->whereNull('result')->sum('bet');
        $available_money = $auth_user->money - $open_bet;

        if ($invite->invitee_id !== $auth_user->id) {
            return response()->json(['status' => false,'message' => 'No bet found']);
        }

        if ($invite->result !== null) {
            return response()->json(['status' => false,'message' => 'You already played this bet']);
        }

        if ($invite->bet > $available_money) {
            return response()->json(['status' => false,'message' => 'Not enough money to open bet']);
        }

        $result = rand(0,1) == $invite->flip;

        $coin[0] = "Head";
        $coin[1] = "Tail";

        if ($result) {

            $auth_user->increment('money',$invite->bet);
            $invite->inviter()->decrement('money',$invite->bet);

            $result_str = "won";
        } else {

            $auth_user->decrement('money',$invite->bet);
            $invite->inviter()->increment('money',$invite->bet);

            $result_str = "lose";

        }
        $invite->update(['result' => $result]);

        $inviter_result = $coin[$invite->flip];
        $user_result = $coin[$result];

        return response()->json(['status' => true,'message' => "$inviter->name:$inviter_result, you: $user_result. You $result_str"]);
    }

    public function rejectBet(Invitation $invite) {

        $auth_user = Auth::user();

        if ($invite->invitee_id !== $auth_user->id) {
            return response()->json(['status' => false,'message' => 'No bet found']);
        }

        if ($invite->result !== null) {
            return response()->json(['status' => false,'message' => 'You already played this bet']);
        }

        $invite->update(['result' => -1]);

        return response()->json(['status' => true,'message' => "Bet rejected"]);
    }

    public function myBets(Invitation $invite) {

        $bets = Auth::user()->bets()->with('inviter:id,name,email')->whereNull('result')->get();

        return response()->json(['status' => true,'bets' => $bets]);
    }
}
