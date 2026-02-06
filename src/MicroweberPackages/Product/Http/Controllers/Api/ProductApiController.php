<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 8/19/2020
 * Time: 4:09 PM
 */

namespace MicroweberPackages\Product\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MicroweberPackages\Admin\Http\Controllers\AdminDefaultController;
use MicroweberPackages\Product\Http\Requests\ProductRequest;
use MicroweberPackages\Product\Http\Requests\ProductCreateRequest;
use MicroweberPackages\Product\Http\Requests\ProductUpdateRequest;
use MicroweberPackages\Product\Repositories\ProductRepository;

class ProductApiController extends AdminDefaultController
{
    public $product;

    public function __construct(ProductRepository $product)
    {
        $this->product = $product;

    }

    /**
    /**
     * Display a listing of the product.
     *
     * @param ProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return (new JsonResource(
            $this->product
                ->filter($request->all())
                ->paginate($request->get('limit', 30))
                ->appends($request->except('page'))

        ))->response();

    }

    /**
     * Store product in database
     *
     * @param ProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductRequest $request)
    {
        $result = $this->product->create($request->all());
        $this->moveDigitalFileToHiddenFolder($result, $request->get('content_data', []));
        return (new JsonResource($result))->response();
    }

    /**
     * Display the specified resource.show
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $result = $this->product->show($id);

        return (new JsonResource($result))->response();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  ProductRequest $request
     * @param  string $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductUpdateRequest $request, $product)
    {

        $result = $this->product->update($request->all(), $product);
        $this->moveDigitalFileToHiddenFolder($result, $request->get('content_data', []));
        return (new JsonResource($result))->response();
    }

    /**
     * Destroy resources by given id.
     *
     * @param string $id
     * @return void
     */
    public function destroy($id)
    {
        return (new JsonResource(['id'=>$this->product->delete($id)]));
    }

    private function moveDigitalFileToHiddenFolder($product, array $contentData): void
    {
        $digitalFile = data_get($contentData, 'digital_file');
        if (!$digitalFile || !is_string($digitalFile)) {
            return;
        }

        if (strpos($digitalFile, '/userfiles/media/digital-downloads/') !== false) {
            return;
        }

        $sourcePath = $this->normalizeFilePath($digitalFile, app()->url_manager->to_path($digitalFile));
        if (!$sourcePath || !is_file($sourcePath)) {
            return;
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $basename = 'digital-' . $product->id . '-' . uniqid();
        $filename = $basename . ($extension ? '.' . $extension : '');

        $baseDir = userfiles_path() . 'media' . DS . 'digital-downloads';
        $targetDir = $baseDir . DS . 'products' . DS . $product->id;

        if (!is_dir($targetDir)) {
            mkdir_recursive($targetDir);
        }

        $this->ensureHiddenFolderAccessBlocked($baseDir);

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
        $moved = @rename($sourcePath, $targetPath);
        if (!$moved) {
            if (@copy($sourcePath, $targetPath)) {
                @unlink($sourcePath);
                $moved = true;
            }
        }

        if (!$moved) {
            return;
        }

        $newUrl = userfiles_url() . 'media/digital-downloads/products/' . $product->id . '/' . $filename;
        app()->content_manager->save_content_data_field([
            'rel_type' => $product->getMorphClass(),
            'rel_id' => $product->id,
            'field_name' => 'digital_file',
            'field_value' => $newUrl,
        ]);
    }

    private function normalizeFilePath(string $fileUrl, $filePath): ?string
    {
        if (is_string($filePath) && $filePath !== '' && filter_var($filePath, FILTER_VALIDATE_URL)) {
            $filePath = '';
        }

        if (!$filePath || !is_string($filePath)) {
            $pathFromUrl = $this->extractPathFromUrl($fileUrl);
            if ($pathFromUrl) {
                $filePath = public_path(ltrim($pathFromUrl, '/'));
            }
        }

        if (!$filePath || !is_string($filePath)) {
            return null;
        }

        $real = realpath($filePath);
        if (!$real) {
            return null;
        }

        $publicRoot = realpath(public_path());
        if ($publicRoot && strpos($real, $publicRoot) !== 0) {
            return null;
        }

        return $real;
    }

    private function extractPathFromUrl(string $fileUrl): ?string
    {
        if ($fileUrl === '') {
            return null;
        }

        $parts = @parse_url($fileUrl);
        if (is_array($parts) && isset($parts['path'])) {
            return $parts['path'];
        }

        if (preg_match('#^https?:(/.*)$#i', $fileUrl, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function ensureHiddenFolderAccessBlocked(string $baseDir): void
    {
        $htaccessPath = $baseDir . DIRECTORY_SEPARATOR . '.htaccess';
        if (is_file($htaccessPath)) {
            return;
        }

        if (!is_dir($baseDir)) {
            mkdir_recursive($baseDir);
        }

        $rules = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n";
        @file_put_contents($htaccessPath, $rules);
    }
}
