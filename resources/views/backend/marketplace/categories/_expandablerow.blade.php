@php
    $padding = max(0, (int)$level) * 20;
@endphp

<tr
    wire:key="cat-{{ $item->id }}"
    class="table-tr child-row is-hidden"
    id="row-{{ $item->id }}"
    data-id="{{ $item->id }}"
    data-parent="{{ $parentId }}"
    style="{{ $item->parent_id !== Null ? 'display : none' : '' }}" data-parent-id="{{ $item->parent_id !== Null ? $item->parent_id : ''  }}"
>
    {{-- checkbox (policy-based) --}}
    {{-- @if($enableCheckbox ?? true)
        @can('delete', $item)
            <td class="table-td table-td-checkbox" wire:ignore>
                <input
                    type="checkbox"
                    class="item-checkbox form-checkbox"
                    value="{{ $item->id }}"
                />
            </td>
        @endcan
    @endif --}}
    <td class="table-td table-td-checkbox" wire:ignore>
                <input
                    type="checkbox"
                    class="item-checkbox form-checkbox"
                    value="{{ $item->id }}"
                />
            </td>
    {{-- data cells --}}
    @foreach($headers ?? [] as $header)
        <td class="table-td {{ isset($header['align']) ? 'text-' . $header['align'] : '' }}">
            @php
                $pascalCaseId = collect(explode('_', $header['id']))->map(fn($part) => ucfirst($part))->implode('');
                $content = $item->{$header['id']} ?? '';
                if ($enableLivewire) {
                    $autoDiscoverableMethodName = 'render' . $pascalCaseId . 'Column';
                    if (isset($header['renderContent']) && is_string($header['renderContent'])) {
                        $content = $this->{$header['renderContent']}($item, $header);
                    } elseif (isset($header['renderRawContent'])) {
                        $content = $header['renderRawContent'];
                    } elseif (method_exists($this, $autoDiscoverableMethodName)) {
                        $content = $this->{$autoDiscoverableMethodName}($item, $header);
                    }
                }
            @endphp

            @if($loop->first)
                <span style="padding-left: {{ $padding }}px"></span>
                @if(!empty($item->children) && count($item->children) > 0)
                    <button type="button"
                            data-terget-id="{{ $item->id}}"
                            wire:ignore
                            class="js-toggle mr-2 text-xs text-primary align-middle togggler"
                            data-id="{{ $item->id }}"
                            data-state="collapsed"
                            aria-expanded="false"
                            aria-controls="row-children-{{ $item->id }}">
                        <span class="icon-plus" data-terget-id="{{ $item->id}}">➕</span>
                        <span class="icon-minus" data-terget-id="{{ $item->id}}" style="display:none;">➖</span>
                    </button>
                @endif
            @endif

            {!! $content !!}
        </td>
    @endforeach
</tr>

{{-- recurse --}}
@foreach(($item->children ?? []) as $child)
    @include('backend.marketplace.categories._expandablerow', [
        'item' => $child,
        'headers' => $headers,
        'level' => $level + 1,
        'enableCheckbox' => $enableCheckbox,
        'parentId' => $item->id
    ])
@endforeach
