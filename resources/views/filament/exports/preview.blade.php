@props(['headers', 'rows', 'total', 'shown'])

<div class="space-y-3">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Showing the first {{ number_format($shown) }} of {{ number_format($total) }}
        {{ \Illuminate\Support\Str::plural('row', $total) }} that match the current filters.
        The downloaded file contains all {{ number_format($total) }} and lets you choose the columns.
    </p>

    @if ($shown === 0)
        <p class="rounded-lg bg-gray-50 p-6 text-center text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">
            Nothing to export for the current filters.
        </p>
    @else
        <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
            <table class="w-full table-auto divide-y divide-gray-200 text-start text-sm dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        @foreach ($headers as $header)
                            <th class="whitespace-nowrap px-3 py-2 text-start font-semibold text-gray-950 dark:text-white">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @foreach ($rows as $row)
                        <tr>
                            @foreach ($row as $value)
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $value === null || $value === '' ? '—' : $value }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
