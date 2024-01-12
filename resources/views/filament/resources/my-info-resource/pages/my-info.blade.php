<x-filament-panels::page>
    {{-- Profile Section Start  --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <div class=" border border-[#D1D5D8] rounded bg-white">
        <div class=" flex items-end gap-x-16 p-6">
            <div class=" flex items-center gap-x-2.5">
                <img src="/images/profile.png" alt="" class=" w-20 h-20">
                <div>
                    <h1 class=" text-[#09101D] font-medium text-xl">Sridharan</h1>
                    <h1 class=" text-[#6B7280] font-medium mt-1">UI/UX Designer</h1>
                </div>
            </div>
            <div class=" flex items-center gap-x-3 pb-3">
                <img src="/icon/birthday.svg" alt="">
                <h3 class=" text-[#6B7280]">31 Oct, 2000</h3>
            </div>
            <div class=" flex items-center gap-x-3 pb-3">
                <img src="/icon/roll-no.svg" alt="">
                <h3 class=" text-[#6B7280]">SF0001</h3>
            </div>
            <div class=" flex items-center gap-x-3 pb-3">
                <img src="/icon/bag.svg" alt="">
                <h3 class=" text-[#6B7280]">1 year 03 months</h3>
            </div>
        </div>

        {{-- Department Section start  --}}
        <div class=" border-t border-[#D1D5D8] flex items-center gap-x-16 px-6 py-5">
            <div>
                <h3 class=" font-medium">Department</h3>
                <h3 class=" text-[#6B7280] mt-1">Design</h3>
            </div>
            <div>
                <h3 class=" font-medium">Phone number</h3>
                <h3 class=" text-[#6B7280] mt-1">+91 9597308088</h3>
            </div>
            <div>
                <h3 class=" font-medium">Employee type</h3>
                <h3 class=" text-[#6B7280] mt-1">Full time</h3>
            </div>
            <div>
                <h3 class=" font-medium">Email</h3>
                <h3 class=" text-[#6B7280] mt-1">sridharan@saasforest.com</h3>
            </div>
            <div>
                <h3 class=" font-medium">Address</h3>
                <h3 class=" text-[#6B7280] mt-1">1/103, A, Main St, Madurai</h3>
            </div>
        </div>
        {{-- Department Section end  --}}
    </div>
    {{-- Profile Section end  --}}

    {{-- Personal Information Section start  --}}
    <div class=" flex mt-5" x-data="{
        val : 1,
    }">
        {{-- List Section Start  --}}
        <div class=" w-1/3" x-data="{
            value : 1,
            active : 'text-[#104FFF] font-medium border-l-[4px] border-[#104FFF] pl-4 py-3 cursor-pointer',
            inactive : 'text-[#6B7280] border-l border-[#D1D5D8] pl-4 flex flex-col py-3 cursor-pointer w-full'
        }">
            <ul class="">
                <li @click="val = 1" :class="val == '1' ? active : inactive">Personal Information</li>
                <li @click="val = 2" :class="val == '2' ? active : inactive">Address</li>
                <li @click="val = 3" :class="val == '3' ? active : inactive">Contact Info</li>
                <li @click="val = 4" :class="val == '4' ? active : inactive">Compensation</li>
                <li @click="val = 5" :class="val == '5' ? active : inactive">Bank Info</li>
                <li @click="val = 6" :class="val == '6' ? active : inactive">Education</li>
                <li @click="val = 7" :class="val == '7' ? active : inactive">Assets</li>
                <li @click="val = 8" :class="val == '8' ? active : inactive">Timesheets</li>
            </ul>
        </div>
        {{-- List Section end  --}}

        {{-- Form Section Start  --}}
        <div class=" w-full bg-white">
            <div class=" border border-[#D1D5D8] rounded w-full pb-14">
                <div class=" flex justify-between items-center mx-3 my-3 px-3 py-3 bg-[#FAFBFB]">
                    <h1 class=" text-xl text-[#09101D] font-medium">Personal Information</h1>
                    <div class=" flex items-end cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none">
                            <path
                                d="M21.3113 6.87846L17.1216 2.68971C16.9823 2.55038 16.8169 2.43986 16.6349 2.36446C16.4529 2.28905 16.2578 2.25024 16.0608 2.25024C15.8638 2.25024 15.6687 2.28905 15.4867 2.36446C15.3047 2.43986 15.1393 2.55038 15 2.68971L3.43969 14.25C3.2998 14.3888 3.18889 14.554 3.11341 14.736C3.03792 14.9181 2.99938 15.1133 3.00001 15.3103V19.5C3.00001 19.8978 3.15804 20.2794 3.43935 20.5607C3.72065 20.842 4.10218 21 4.50001 21H20.25C20.4489 21 20.6397 20.921 20.7803 20.7803C20.921 20.6397 21 20.4489 21 20.25C21 20.0511 20.921 19.8603 20.7803 19.7197C20.6397 19.579 20.4489 19.5 20.25 19.5H10.8113L21.3113 9.00002C21.4506 8.86073 21.5611 8.69535 21.6365 8.51334C21.7119 8.33133 21.7507 8.13625 21.7507 7.93924C21.7507 7.74222 21.7119 7.54714 21.6365 7.36513C21.5611 7.18312 21.4506 7.01775 21.3113 6.87846ZM8.68969 19.5H4.50001V15.3103L12.75 7.06033L16.9397 11.25L8.68969 19.5ZM18 10.1897L13.8113 6.00002L16.0613 3.75002L20.25 7.93971L18 10.1897Z"
                                fill="#5E5E5E" />
                        </svg>
                        <h3 class=" text-[#5E5E5E]">Edit</h3>
                    </div>
                </div>
            
                <div class=" px-6">
                    <form wire:submit="create" x-show="val == 1">
                        {{ $this->form }}
                    </form>
                    <form wire:submit="addressCreate" x-show="val == 2">
                        {{ $this->addressForm }}
                    </form>
                </div>
            </div>
            
            {{-- Button Section start  --}}
            <div class=" flex items-center gap-x-2 mt-6">
                <button class=" text-white font-bold bg-[#104FFF] px-[22px] py-2 rounded-lg">Save</button>
                <button class=" border border-[#D1D5D8] bg-white px-[22px] py-2 rounded-lg">Cancel</button>
            </div>
            {{-- Button Section end  --}}
        </div>
        {{-- Form Section end  --}}
    </div>
    {{-- Personal Information Section end  --}}


</x-filament-panels::page>
