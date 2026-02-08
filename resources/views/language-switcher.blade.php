<div @if($floating ?? false) style="position: fixed; top: 0.5rem; right: 1rem; z-index: 50;" @endif>
    <x-filament::dropdown placement="bottom-start" maxHeight="36rem">
        <x-slot name="trigger" style="justify-self: center; align-self: center; padding: 0.5rem 0;">
            @if (isset($currentLanguage) && $showFlags)
                <x-filament::link tag="button">
                    <div style="width: 2rem; height: 2rem; border-radius: 9999px; overflow: hidden;">
                        @php
                            try {
                                echo svg('flag-1x1-'.$currentLanguage['flag'], '')->toHtml();
                                $flagFound = true;
                            } catch (Exception) {
                                $flagFound = false;
                            }
                        @endphp
                        @unless ($flagFound)
                            <x-filament::icon icon="heroicon-o-language" style="width: 2rem; height: 2rem;" />
                        @endunless
                    </div>
                </x-filament::link>
            @else
                <x-filament::icon-button icon="heroicon-o-language" label="Language switcher"/>
            @endif
        </x-slot>

        <x-filament::dropdown.list style="max-height: 20rem; overflow-y: auto;">
            @foreach ($otherLanguages as $language)
                @php
                    $isCurrent = false;
                    if (isset($currentLanguage)) {
                        $isCurrent = $currentLanguage['code'] === $language['code'];
                    }
                @endphp
                <x-filament::dropdown.list.item :href="route('filament-language-switcher.switch', ['code' => $language['code']])" tag="a">
                    <span class="fi-dropdown-list-item-label" style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap; width: 100%; text-align: left; display: flex; justify-content: flex-start; gap: 0.75rem;">
                        @if ($showFlags)
                            <div style="width: 1.5rem; height: 1.5rem; flex-shrink: 0;">
                                @php
                                    try {
                                        echo svg('flag-4x3-'.$language['flag'], '')->toHtml();
                                        $itemFlagFound = true;
                                    } catch (Exception) {
                                        $itemFlagFound = false;
                                    }
                                @endphp
                                @unless ($itemFlagFound)
                                    <x-filament::icon icon="heroicon-o-flag" style="width: 1.5rem; height: 1.5rem;" />
                                @endunless
                            </div>
                            <span>{{ $language['name'] }}</span>
                        @else
                            <span style="{{ $isCurrent ? 'font-weight: 600;' : '' }}">
                                {{ $language['name'] }}
                            </span>
                        @endif
                    </span>
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
