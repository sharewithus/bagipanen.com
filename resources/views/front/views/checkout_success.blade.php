@extends('front.layouts.app')
@section('title', 'Voila')

@section('content')
<section class="max-w-[640px] w-full min-h-screen mx-auto flex flex-col bg-[#FCF7F1] overflow-x-hidden items-center justify-center px-6 py-[100px]">
    <div class="flex flex-col gap-1 text-center">
        <h1 class="font-bold text-[24px] leading-[36px]">Terimakasih, kamu sudah berdonasi</h1>
        <p class="font-extrabold text-[48px] leading-[72px] text-[#76AE43]">Rp {{number_format($totalAmountDonation, 0, ',','.')}}</p>
    </div>
    <div class="flex flex-col bg-white border border-[#EBECF0] rounded-2xl p-[14px] gap-[14px] w-[258px] mt-[30px]">
        <div class="w-[230px] h-[180px] flex shrink-0 overflow-hidden rounded-2xl bg-[#D9D9D9]">
            <img src="{{Storage::url($fundraising->thumbnail)}}" class="w-full h-full object-cover" alt="thumbnail">
        </div>
        <div class="flex flex-col gap-[6px]">
            <p class="font-bold">{{$fundraising->name}}</p>
            <p class="text-xs">Target <span class="font-bold text-[#FF7815]">Rp {{number_format($fundraising->target_amount, 0, ',','.')}}</span></p>
        </div>
    </div>
    <p class="leading-[32px] mt-[30px] text-center">Every penny you gave were impactful for <br> those who might need it, thank you.</p>
    <div class="w-[220px] mx-auto flex flex-col gap-5 mt-[50px]">
        <a href="{{route('front.details', $fundraising)}}" class="p-[14px_20px] bg-[#76AE43] rounded-full text-white w-full mx-auto font-semibold hover:shadow-[0_12px_20px_0_#76AE4380] transition-all duration-300 text-nowrap text-center">Lihat Detail</a>
        <a href="https://wa.me/+6285697755734" target="__blank" class="p-[14px_20px] bg-[#1E2037] rounded-full text-white w-full mx-auto font-semibold text-nowrap text-center">Kontak CS Kami</a>
    </div>
</section>
@endsection