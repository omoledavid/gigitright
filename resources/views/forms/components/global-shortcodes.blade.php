@php
    $shortcodes = json_decode($this->data['global_shortcodes'], true);
@endphp

@if (!empty($shortcodes))
    <table class="w-full text-sm text-left border border-gray-200 rounded">
        <thead style="color: black" class="bg-blue-100">
        <tr>
            <th class="px-4 py-2 bg-gray-100">Shortcode</th>
            <th class="px-4 py-2 bg-gray-100">Description</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($shortcodes as $shortCode => $codeDetails)
            <tr>
                <td class="px-4 py-2 text-blue-600 font-mono">  {{'{{'.$shortCode}}}}</td>
                <td class="px-4 py-2">{{ $codeDetails }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="text-sm text-gray-500 italic">No shortcodes available.</p>
@endif
