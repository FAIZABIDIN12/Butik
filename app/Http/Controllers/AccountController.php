<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:accounts,code',
            'name' => 'required|string',
            'position' => 'required|in:asset,liability,revenue,expense',
            'initial_balance' => 'required|numeric',
            'current_balance' => 'required|numeric',
        ]);

        Account::create($request->all());
        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit($code)
    {
        $account = Account::findOrFail($code);
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, $code)
    {
        $request->validate([
            'code' => 'required|string|unique:accounts,code',
            'name' => 'required|string',
            'position' => 'required|in:asset,liability,revenue,expense',
            'initial_balance' => 'required|numeric',
            'current_balance' => 'required|numeric',
        ]);

        $account = Account::findOrFail($code);
        $account->update($request->all());
        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy($code)
    {
        $account = Account::findOrFail($code);
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
    }
}
