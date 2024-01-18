<script src="https://cdn.tailwindcss.com"></script>
<div class=" w-full">
    {{-- {{ $getState() }} --}}
    {{-- <h1>Velu</h1> --}}
  
    {{-- bal;achandran --}}
    <div class=" flex justify-end">
        <button class=" text-sm text-[#287D3C] border border-[#287D3C] rounded px-1">Active</button>
    </div>
    {{-- <div class="flex justify-end"><p class="ml-3">active</p></div> --}}
    <div class=" flex justify-center">
        <img src="/images/profile.png" alt="">
    </div>
    <div class=" text-center mt-2">
        <div><span class=" text-[#6B7280] font-medium">EMP001</span> <span class=" font-medium">-   {{$getRecord()->name }}</span></div>
        <h3 class=" text-sm text-[#6B7280] mt-1">Sales Manager</h3>
    </div>
    <div class=" bg-[#F5F8FF] py-2 px-3 mt-5 w-full rounded">
        <div class=" flex items-center gap-x-3 w-full">
            <img src="/icon/profile-2.svg" alt="">
            <h4>UI/UX Designer</h4>
        </div>
        <div class=" flex items-center gap-x-3 mt-3 w-full">
            <img src="/icon/message.svg" alt="">
            <h4 class=" truncate">jan.schmidt@example.com</h4>
        </div>
        <div class=" flex items-center gap-x-3 mt-3 w-full">
            <img src="/icon/phone.svg" alt="">
            <h4>+1 555-123-4567</h4>
        </div>
    </div>
</div>
