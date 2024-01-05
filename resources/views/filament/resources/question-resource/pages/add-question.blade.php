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

            /* Multi Select  */
        </style>

        <form wire:submit="{{ $updateVal ? 'updateQuestion' : 'createQuestion' }}">
            <div class=" border border-[#D1D5DB] rounded-xl p-6">
                <div>
                    <label for="" class=" text-[#374151] font-semibold">What question do you want to ask?</label>
                    <input type="text" wire:model="description"
                        class=" block border border-[#D1D5DB] bg-white px-5 py-2 mt-1 shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)] rounded-lg w-full">
                    @error('description')
                        <span class=" text-red-600 block mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class=" mt-6">
                    <h3 for="" class=" text-[#374151] font-semibold">How often do you want to ask?</h3>
                    @foreach ($this->dailys as $daily)
                        <div class=" flex items-center gap-x-2 mt-5">
                            <input type="radio" name="daily" value="{{ $daily }}" wire:model="status"
                                class=" border border-[#D1D5DB] shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)]">
                            <label for="" class=" text-[#374151]">{{ $daily }}</label>
                        </div>
                    @endforeach
                    @error('status')
                        <span class=" text-red-600 block mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class=" mt-6">
                    <h3 for="" class=" text-[#374151] font-semibold">At what time of the day?</h3>
                    @foreach ($this->times as $time)
                        {{-- @dd($time[1]) --}}
                        {{-- @dd($date , $time[1]) --}}
                        <div class=" flex items-center gap-x-2 mt-5">
                            <input type="radio" name="time" value="{{ $time[0] }}" wire:model="day"
                                :checked="'{{ $date }}' == '{{ $time[1] }}' || '{{ $date }}' ==
                                '{{ $time[1] }}' ? true: false"
                                class=" border border-[#D1D5DB] shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)]">
                            <label for="" class=" text-[#374151]">{{ $time[0] }}</label>
                        </div>
                    @endforeach
                    <div class=" flex items-center gap-x-2 mt-5">
                        <input type="radio" name="time" value="{{ $day1 }}"
                            :checked="'{{ $date }}' != '10:00 AM' && '{{ $date }}' != '06:00 PM' ? true: false"
                            class=" border border-[#D1D5DB] shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)]">
                        <label for="" class=" text-[#374151]">Let me pick a time</label>
                        <input type="time" wire:model="day1"
                            class=" bg-white border border-[#D1D5D8] px-1 h-6 w-[106px] rounded">
                    </div>
                    {{-- @error('day')
                        <span class=" text-red-600 block mt-1">{{ $message }}</span>
                    @enderror --}}
                </div>
                <div class=" mt-6">
                    {{ $this->form }}
                </div>
            </div>

            {{-- Button section start  --}}
            <div class=" flex gap-x-6 items-center mt-7">
                <button type="submit" class=" text-white bg-[#E0BF00] font-medium px-5 py-2 rounded-lg">
                    {{ $updateVal ? 'update' : 'create' }}
                </button>
                <button wire:click="clearQuestion"
                    class=" text-[#1F2937] border border-[#D1D5DB] font-medium px-5 py-2 rounded-lg shadow-[0px_1px_3px_0px_rgba(0,0,0,0.07)]"
                    type="reset">
                    Cancel
                </button>
            </div>
            {{-- Button section end  --}}
        </form>

    </div>
</x-filament-panels::page>
