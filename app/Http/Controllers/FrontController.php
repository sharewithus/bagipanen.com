<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Models\Category;
use App\Models\Donatur;
use App\Models\Fundraiser;
use App\Models\Fundraising;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Midtrans\Config;
use Midtrans\Snap;

class FrontController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }
    public function index()
    {
        $categories = Category::all();
        $fundraisings = Fundraising::with([
            'category',
            'fundraiser'
        ])
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->get();

        return view('front.views.index', compact('categories', 'fundraisings'));
    }
    public function category(Category $category)
    {
        return view('front.views.category', compact('category'));
    }

    public function details(Fundraising $fundraising)
    {
        $goalReached = $fundraising->totalReachAmount() >= $fundraising->target_amount;
        return view('front.views.details', compact('fundraising', 'goalReached'));
    }
    public function support(Fundraising $fundraising)
    {
        return view('front.views.donation', compact('fundraising'));
    }
    public function checkout(Fundraising $fundraising, $totalAmountDonation)
    {
        return view('front.views.checkout', compact('fundraising', 'totalAmountDonation'));
    }

    public function store(StoreDonationRequest $request, Fundraising $fundraising, $totalAmountDonation)
    {
        DB::transaction(function () use ($request, $fundraising, $totalAmountDonation) {
            $validated = $request->validated();

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $validated['fundraising_id'] = $fundraising->id;
            $validated['total_amount'] = $totalAmountDonation;
            $validated['is_paid'] = false;

            $donatur = Donatur::create($validated);
        });

        return view('front.views.checkout_success', compact('fundraising', 'totalAmountDonation'));

        // return redirect()->route('front.details', $fundraising->slug);
    }

    function checkoutSuccess(Fundraising $fundraising, $totalAmountDonation)
    {
        return view('front.views.checkout_success', compact('fundraising', 'totalAmountDonation'));
    }

    public function updateStatus()
    {

        try {
            // Fetch the donation record using the provided ID
            $donatur = Donatur::findOrFail(intval($this->request->id));

            // Update the status to "paid"
            $donatur->is_paid = true;
            $donatur->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Donation status updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update the donation status.',
            ], 500);
        }
    }

    public function submitDonation()
    {
        $validator = \Validator::make(request()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string'],
            // 'proof' => ['required', 'image', 'mimes:png,jpg,jpeg'],
            'notes' => ['required', 'string', 'max:65535'],
        ]);

        if ($validator->fails()) {
            return [
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ];
        }


        $response = []; // Initialize response array

        DB::transaction(function () use (&$response) {
            $validated['name'] = $this->request->name;
            $validated['fundraising_id'] = $this->request->fundraising_id;
            $validated['total_amount'] = $this->request->amount;
            $validated['notes'] = $this->request->notes;
            $validated['is_paid'] = false;
            $validated['phone_number'] = $this->request->phone_number;
            $validated['proof'] = 'proofs/midtrans.php';

            $donatur = Donatur::create($validated);

            // Buat transaksi ke midtrans kemudian save snap tokennya.
            $payload = [
                'transaction_details' => [
                    'order_id'      => rand(),
                    'gross_amount'  => $this->request->amount,
                ],
                'customer_details' => [
                    'first_name'    => $this->request->name,
                    // 'email'         => $donatur->donor_email,
                    'phone'         => $this->request->phone_number,
                    // 'address'       => '',
                ],
                'item_details' => [
                    [
                        'id'       => $this->request->fundraising_id,
                        'price'    => $this->request->amount,
                        'quantity' => 1,
                        'name'     => $this->request->fundraising_slug
                    ]
                ]
            ];

            $snapToken = Snap::getSnapToken($payload);

            // Beri response snap token
            $response['snap_token'] = $snapToken;
            $response['id'] = $donatur->id;;
        });
        return response()->json($response);
        // return response()->json(["test" => "test"]);
    }
}
