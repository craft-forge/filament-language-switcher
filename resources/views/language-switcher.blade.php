<div @if($floating ?? false) style="position: fixed; top: 0.5rem; right: 1rem; z-index: 50;" @endif>
    <x-filament::dropdown placement="bottom-start" maxHeight="36rem">

        {{-- Trigger --}}
        <x-slot name="trigger" style="justify-self: center; align-self: center; padding: 0.5rem 0;">
            <x-filament::link tag="button">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    @if (isset($currentLanguage) && $showFlags)
                        <div style="width: 2rem; height: 2rem; border-radius: 9999px; overflow: hidden;">
                            @php
                                try {
                                    echo svg('flag-1x1-' . $currentLanguage['flag'], '')->toHtml();
                                    $flagFound = true;
                                } catch (Exception) {
                                    $flagFound = false;
                                }
                            @endphp
                            @unless ($flagFound)
                                <x-filament::icon icon="heroicon-o-language" style="width: 2rem; height: 2rem;" />
                            @endunless
                        </div>
                    @else
                        <x-filament::icon icon="heroicon-o-language" style="width: 2rem; height: 2rem;" />
                    @endif

                    @if(isset($currentLanguage))
                        <span style="font-weight: 600;">
                            {{ $currentLanguage['name'] }}
                        </span>
                    @endif
                </div>
            </x-filament::link>
        </x-slot>

        @php
            $languages = collect([$currentLanguage ?? null])
                ->filter()
                ->merge($otherLanguages ?? [])
                ->unique('code')
                ->values();
        @endphp

        {{-- Dropdown List --}}
        <x-filament::dropdown.list style="max-height: 20rem; overflow-y: auto;">
            @foreach ($languages as $language)
                @php
                    $isCurrent = isset($currentLanguage) && $currentLanguage['code'] === $language['code'];
                    $switchUrl = route('filament-language-switcher.switch', ['code' => $language['code']]);
                @endphp

                <x-filament::dropdown.list.item>
                    <a href="{{ $switchUrl }}" style="display:block; width:100%; transition: opacity .2s ease;" onclick="
                                            event.preventDefault();
                                            this.style.opacity = '0.6';
                                            this.style.pointerEvents = 'none';
                                            window.location.href = this.href;
                                        ">
                        <span class="fi-dropdown-list-item-label"
                            style="display:flex;align-items:center;gap:.75rem;width:100%;">

                            {{-- Active Checkmark --}}
                            <span style="width:1.25rem;display:inline-flex;justify-content:center;">
                                @if ($isCurrent)
                                    <x-filament::icon icon="heroicon-m-check" style="width: 1.25rem; height: 1.25rem;" />
                                @endif
                            </span>

                            {{-- Flag --}}
                            @if ($showFlags)
                                <div style="width:1.5rem;height:1.5rem;flex-shrink:0;">
                                    @php
                                        try {
                                            echo svg('flag-4x3-' . $language['flag'], '')->toHtml();
                                            $itemFlagFound = true;
                                        } catch (Exception) {
                                            $itemFlagFound = false;
                                        }
                                    @endphp
                                    @unless ($itemFlagFound)
                                        <x-filament::icon icon="heroicon-o-flag" style="width: 1.5rem; height: 1.5rem;" />
                                    @endunless
                                </div>
                            @endif

                            {{-- Language Name --}}
                            <span style="{{ $isCurrent ? 'font-weight:600;' : '' }}">
                                {{ $language['name'] }}
                            </span>
                        </span>
                    </a>
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>

@if(session('filament-locale-changed'))
@php $localeEvent = session('filament-locale-changed'); @endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.dispatchEvent(new CustomEvent('filament-locale-changed', {
            detail: {
                newLocale: '{{ $localeEvent['newLocale'] }}',
                oldLocale: '{{ $localeEvent['oldLocale'] }}'
            }
        }));
    });
</script>
@endif
