 <div x-data="{ editing: false }">
     @if ($isWorkbench)
         <flux:badge size="lg" icon="wrench-screwdriver" variant="outline">
             Workbench
         </flux:badge>
     @endif
     {{-- @if ($icon)
             <flux:icon name="{{ $icon }}" class="size-9 {{ $color }}" />
         @endif --}}

     <div class="relative">
         <flux:heading x-show="!editing" x-transition size="2xl" class="{{ $color }}! capitalize text-nowrap"
             weight="semibold">
             {{ $title }}
         </flux:heading>
         @if ($editable)
             <div x-show="editing" x-cloak x-transition
                 class="absolute left-0 top-1/2 -translate-y-1/2 flex items-center gap-(--gap-micro)  pt-1">
                 <flux:input class="w-44!" placeholder="Project name" value="{{ $title }}" />
                 <flux:button variant="ghost" icon="check" x-on:click="editing = false" />
             </div>
         @endif
     </div>
     @if ($editable)
         <flux:button x-show="!editing" x-transition variant="ghost" x-on:click="editing = true"
             class="opacity-0 group-hover:opacity-100 transition-opacity" />
     @endif
     {{-- <flux:spacer />
         @if ($button)
             <flux:button label="{{ $button }}" variant="soft" color="primary" icon="{{ $buttonIcon }}"
                 iconVariant="duotone" />
         @endif --}}

 </div>
