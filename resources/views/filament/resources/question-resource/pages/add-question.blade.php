<x-filament-panels::page>
    <div>
        <style>
            input[type="number"]::-webkit-outer-spin-button,
            input[type="number"]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            input[type="number"] {
                -moz-appearance: textfield;
            }

            :root {
                --theme-color: #ff7f27;
                --theme-color-hover: #fc914a;
                --theme-color2: #000c7b;
            }

            /* Modal Box  */

            .modal-box {
                width: 100%; 
                
            }

            /* Custom Multi Select */
            .sd-multiSelect {
                position: relative;
                border-radius: 8px;
                box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.07);
            }

            .sd-multiSelect .placeholder {
                cursor: pointer;
            }

            .sd-multiSelect .ms-offscreen {
                height: 1px;
                width: 1px;
                opacity: 0;
                overflow: hidden;
                display: none;
            }

            .sd-multiSelect .sd-CustomSelect {
                width: 100% !important;
            }

            .sd-multiSelect .ms-choice {
                position: relative;
                padding-left: 20px;
                text-align: left !important;
                width: 100%;
                border: 1px solid #D1D5DB;
                background: #ffff;
                box-shadow: none;
                font-size: 15px;
                height: 44px;
                font-weight: 500;
                color: #212529;
                line-height: 1.5;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                border-radius: 0.25rem;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }

            .sd-multiSelect .ms-choice:after {
                content: "\f107 ";
                font-family: "FontAwesome";
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 18px;
            }

            .sd-multiSelect .ms-choice:focus {
                border-color: var(--theme-color);
            }

            .sd-multiSelect .ms-drop.bottom {
                display: none;
                background: #fff;
                border: 1px solid #e5e5e5;
                padding: 10px;
            }

            .sd-multiSelect .ms-drop li {
                position: relative;
                margin-bottom: 10px;
            }

            .sd-multiSelect .ms-drop li input[type="checkbox"] {
                padding: 0;
                height: initial;
                width: initial;
                margin-bottom: 0;
                display: none;
                cursor: pointer;
            }

            .sd-multiSelect .ms-drop li label {
                cursor: pointer;
                user-select: none;
                -ms-user-select: none;
                -moz-user-select: none;
                -webkit-user-select: none;
            }

            .sd-multiSelect .ms-drop li label:before {
                content: "";
                -webkit-appearance: none;
                background-color: transparent;
                border: 2px solid var(--theme-color);
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05),
                    inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05);
                padding: 8px;
                display: inline-block;
                position: relative;
                vertical-align: middle;
                cursor: pointer;
                margin-right: 5px;
            }

            .sd-multiSelect .ms-drop li input:checked+span:after {
                content: "";
                display: block;
                position: absolute;
                top: 9px;
                left: 5px;
                width: 10px;
                height: 10px;
                background: var(--theme-color);
                border-width: 0 2px 2px 0;
            }
        </style>
        {{-- Add New Question section start  --}}
        <div>
            <div class=" border border-[#D1D5DB] rounded-xl p-6">
                <div>
                    <label for="" class=" text-[#374151]">What question do you want to ask?</label>
                    <input type="text" wire:model="description"
                        class=" block border border-[#D1D5DB] bg-white px-5 py-2 mt-1 shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)] rounded-lg w-full">
                </div>
                <div class=" mt-6">
                    <h3 for="" class=" text-[#374151]">How often do you want to ask?</h3>
                    @foreach ($this->dailys as $daily)
                        <div class=" flex items-center gap-x-2 mt-5">
                            <input type="radio" name="daily" value="{{$daily}}" wire:model="status"
                                class=" border border-[#D1D5DB] shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)]">
                            <label for="" class=" text-[#374151]">{{ $daily }}</label>
                        </div>
                    @endforeach
                </div>
                <div class=" mt-6">
                    <h3 for="" class=" text-[#374151]">At what time of the day?</h3>
                    @foreach ($this->times as $time)
                        <div class=" flex items-center gap-x-2 mt-5">
                            <input type="radio" name="time" value="{{$time}}" wire:model="day"
                                class=" border border-[#D1D5DB] shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)]">
                            <label for="" class=" text-[#374151]">{{ $time }}</label>
                        </div>
                    @endforeach
                    <div class=" flex items-center gap-x-2 mt-5">
                        <input type="radio" name="time" value="{{$day}}" wire:model="day"
                            class=" border border-[#D1D5DB] shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)]">
                        <label for="" class=" text-[#374151]">Let me pick a time</label>
                        <input wire:model="day1" type="time" class=" bg-white border border-[#D1D5D8] px-1 h-6 w-[106px] rounded">
                    </div>
                </div>
                <div class=" mt-6">
                    <label for="lists" class=" text-[#374151]">What question do you want to ask?</label>
                    <div class="modal-box mt-1">
                        <div class="sd-multiSelect form-group">
                            <select multiple id="current-job-role" class="sd-CustomSelect" wire:model="userName">
                                @foreach ($this->users as $user)
                                    <option value="{{$user->name}}">{{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Button section start  --}}
            <div class=" flex gap-x-6 items-center mt-7">
                <button wire:click="createQuestion" class=" text-white bg-[#E0BF00] font-medium px-5 py-2 rounded-lg">
                    Create
                </button>
                <button
                    class=" text-[#1F2937] border border-[#D1D5DB] font-medium px-5 py-2 rounded-lg shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)]"
                    type="reset">
                    Cancel
                </button>
            </div>
            {{-- Button section end  --}}
        </div>

        {{-- Add New Question section end  --}}

       

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="https://bsite.net/savrajdutta/cdn/multi-select.js"></script>
        <script>
            $(document).ready(function() {
                $(".sd-CustomSelect").multipleSelect({
                    selectAll: false,
                    onOptgroupClick: function(view) {
                        $(view).parents("label").addClass("selected-optgroup");
                    }
                });
            });
        </script>
    </div>
</x-filament-panels::page>
