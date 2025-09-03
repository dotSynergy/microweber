<div class="p-6">
    <div class="mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            Upload Multiple Images
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Select multiple images and they will be uploaded automatically. The modal will close once upload is complete.
        </p>
    </div>

    <div x-data="{
        acceptedFileTypes: 'image/*',
        isUploading: false,
        uploadComplete: false
    }">
        <div class="w-full flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 min-h-[200px]">
            
            <div x-show="!isUploading && !uploadComplete" class="text-center p-8">
                <button
                    class="w-full flex flex-col items-center justify-center"
                    type="button" 
                    x-on:click="() => {
                        isUploading = true;
                        
                        mw.filePickerDialog({
                            pickerOptions: {
                                multiple: true,
                                type: acceptedFileTypes,
                            }
                        }, (urls) => {
                            if (!urls || (Array.isArray(urls) && urls.length === 0)) {
                                isUploading = false;
                                return;
                            }
                            
                            // Handle both single URL string and array of URLs
                            let urlArray = Array.isArray(urls) ? urls : [urls];
                            let validUrls = [];
                            
                            urlArray.forEach((singleUrl) => {
                                if (typeof singleUrl === 'string' && singleUrl.trim() !== '') {
                                    validUrls.push({
                                        fileUrl: singleUrl,
                                        fileUrlShort: singleUrl.split('/').pop()
                                    });
                                } else if (typeof singleUrl === 'object' && singleUrl.fileUrl && singleUrl.fileUrl.trim() !== '') {
                                    validUrls.push({
                                        fileUrl: singleUrl.fileUrl,
                                        fileUrlShort: singleUrl.fileUrlShort || singleUrl.fileUrl.split('/').pop()
                                    });
                                }
                            });
                            
                            if (validUrls.length > 0) {
                                // Call the Livewire method to upload files
                                $wire.uploadMultipleImages(validUrls).then(() => {
                                    uploadComplete = true;
                                    setTimeout(() => {
                                        // Close modal after showing success
                                        $wire.dispatch('close-modal', { id: 'upload-multiple-images' });
                                    }, 1500);
                                });
                            } else {
                                isUploading = false;
                            }
                        });
                    }"
                >
                    <div class="mb-4">
                        <svg fill="currentColor" class="w-16 fill-gray-400" viewBox="0 -1.5 35 35" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <path d="M29.426 15.535c0 0 0.649-8.743-7.361-9.74-6.865-0.701-8.955 5.679-8.955 5.679s-2.067-1.988-4.872-0.364c-2.511 1.55-2.067 4.388-2.067 4.388s-5.576 1.084-5.576 6.768c0.124 5.677 6.054 5.734 6.054 5.734h9.351v-6h-3l5-5 5 5h-3v6h8.467c0 0 5.52 0.006 6.295-5.395 0.369-5.906-5.336-7.070-5.336-7.070z"></path>
                        </svg>
                    </div>
                    <div class="text-center">
                        <span class="text-lg">Click to Select Multiple Images</span>
                        <p class="text-gray-500 mt-2">Images will be uploaded automatically</p>
                    </div>
                </button>
            </div>

            <div x-show="isUploading && !uploadComplete" class="text-center p-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mx-auto mb-4"></div>
                <p class="text-gray-600">Uploading images...</p>
            </div>

            <div x-show="uploadComplete" class="text-center p-8">
                <div class="rounded-full h-12 w-12 bg-green-100 mx-auto mb-4 flex items-center justify-center">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <p class="text-green-600 font-medium">Upload completed successfully!</p>
                <p class="text-gray-500 text-sm mt-1">Modal will close automatically...</p>
            </div>
        </div>
    </div>
</div>
