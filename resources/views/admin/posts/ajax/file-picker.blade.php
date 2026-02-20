@php
    $icons = function($mimeType) {
        $icons = [
            'image/jpeg'    => 'image-outline',
            'image/png'     => 'image-outline',
            'image/webp'    => 'image-outline',
            'image/bmp'     => 'image-outline',
            'image/gif'     => 'image-outline',
            'text/plain'    => 'file-document-outline',
            'application/pdf' => 'file-document-outline',
            'application/msword' => 'file-document-outline',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'file-document-outline',
            'application/vnd.oasis.opendocument.text' => 'file-document-outline',
            'application/vnd.ms-excel' => 'file-document-outline',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'file-document-outline',
            'application/vnd.oasis.opendocument.spreadsheet' => 'file-document-outline',
            'text/csv' => 'file-delimited-outline',
            'application/vnd.ms-powerpoint' => 'file-document-outline',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'file-document-outline',
            'application/vnd.oasis.opendocument.presentation' => 'file-document-outline',
            'application/zip' => 'folder-zip-outline',
            'application/x-7z-compressed' => 'folder-zip-outline',
            'application/x-rar-compressed' => 'folder-zip-outline',
            'application/x-arj' => 'folder-zip-outline',
            'video/mp4' => 'file-video-outline',
            'video/x-msvideo' => 'file-video-outline',
            'video/x-matroska' => 'file-video-outline',
            'video/webm' => 'file-video-outline',
            'video/quicktime' => 'file-video-outline',
            'audio/mpeg' => 'file-video-outline',
            'audio/wav' => 'file-video-outline',
            'audio/flac' => 'file-video-outline',
            'audio/aac' => 'file-video-outline',
            'audio/ogg' => 'file-video-outline',
        ];

        // Ellenőrzés, hogy létezik-e az adott mimeType kulcs
        if (array_key_exists($mimeType, $icons)) {
            return $icons[$mimeType];
        }

        // Ha nincs, visszatérünk az alapértelmezett ikonnal
        return 'file-document-outline';
    };

@endphp
<div id="filePickerContent">
    <div id="filePickerContentJsonData" class="hidden"></div>
    <table id="postFilesListTable" class="w-full border-collapse table-auto text-sm sm:text-base">
        <thead>
            <tr class="bg-gray-200">
                <th class="post-selector-col border p-2">
                    <button
                        type="button"
                        name=""
                        id="selectAllFile"
                        class="py-1 px-2 bg-white text-black hover:bg-blue-600 hover:text-white hover:border-white shadow shadow-gray-500 hover:shadow-blue-500 rounded border border-gray-500"
                    >
                        <i class="mdi mdi-checkbox-marked-outline"></i>
                    </button>
                </th>
                <th class="border p-2 max-md:hidden">ID</th>
                <th class="border p-2 md:hidden">További adatok</th>
                <th class="border p-2 max-md:hidden">Cím</th>
                <th class="border p-2 max-md:hidden">Mime típus</th>
                <th class="border p-2 max-md:hidden">Kiterjesztés</th>
            </tr>
        </thead>
        <tbody>
            @forelse($files as $file)
            @php
                $fileExtension = pathinfo($file->file->file_uri, PATHINFO_EXTENSION);
            @endphp
                <tr class="hover:bg-gray-100 hover:text-blue-900" data-id="{{ $file->file->id }}">
                    <td class="border p-2 text-center">
                        <input type="checkbox" name="selected-file[]" value="" class="file-checkbox rounded" />
                    </td>
                    <td class="border p-2 text-center max-md:hidden">{{ $file->file->id }}</td>
                    <td class="border p-3 md:hidden">
                        <div class="grid grid-cols-1 gap-1">
                            <!-- Clickable title -->
                            <div class="mb-2 text-base selectorCol">
                                <span class="inline-block cursor-pointer max-w-full hover:underline max-md:font-bold overflow-wrap break-all whitespace-normal">
                                    <i class="mdi mdi-{{ $icons($file->file->mime_type) }}"></i> {{ $file->file->title }}
                                </span>
                            </div>
                            <!-- Other fields -->
                            <div>
                                <span class="font-semibold">ID:</span> {{ $file->file->id }} |
                            </div>
                            <div>
                                <span class="font-semibold">Dátum:</span> {{ $file->file->mime_type }} |
                            </div>
                            <div>
                                <span class="font-semibold">Kiterjesztés:</span> {{ $fileExtension }} |
                            </div>
                        </div>
                    </td>

                    <td class="selectorCol border p-2 max-md:hidden cursor-pointer items-center flex">
                        <i class="mdi mdi-{{ $icons($file->file->mime_type) }} md-24"></i> <span>{{ $file->file->title }}</span>
                    </td>
                    <td class="border p-2 text-center max-md:hidden">{{ $file->file->mime_type }}</td>
                    <td class="border p-2 text-center max-md:hidden">{{ $fileExtension }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="border p-2 text-center text-gray-500">Nincs találat</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
