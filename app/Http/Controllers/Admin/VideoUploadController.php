<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoUploadController extends Controller
{
    private const MAX_CHUNKS = 20;

    public function chunk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'upload_id' => ['required', 'regex:/^[a-zA-Z0-9-]{20,80}$/'],
            'chunk_index' => ['required', 'integer', 'min:0', 'max:' . (self::MAX_CHUNKS - 1)],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:' . self::MAX_CHUNKS],
            'video_chunk' => ['required', 'file', 'max:56320'],
        ], [
            'chunk_index.max' => 'Số thứ tự phần video vượt quá giới hạn.',
            'total_chunks.max' => 'Video được chia thành quá nhiều phần. Vui lòng tải lại trang và thử lại.',
            'video_chunk.max' => 'Một phần video vượt quá dung lượng cho phép.',
        ]);
        abort_if($data['chunk_index'] >= $data['total_chunks'], 422, 'Chunk không hợp lệ.');

        $directory = $this->directory($request, $data['upload_id']) . '/chunks';
        $request->file('video_chunk')->storeAs($directory, $data['chunk_index'] . '.part', 'local');

        return response()->json(['received' => $data['chunk_index']]);
    }

    public function complete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'upload_id' => ['required', 'regex:/^[a-zA-Z0-9-]{20,80}$/'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:' . self::MAX_CHUNKS],
            'expected_size' => ['required', 'integer', 'min:1', 'max:629145600'],
        ], [
            'total_chunks.max' => 'Video được chia thành quá nhiều phần.',
            'expected_size.max' => 'Video vượt quá giới hạn 600MB.',
        ]);
        $directory = $this->directory($request, $data['upload_id']);
        $finalPath = $directory . '/complete.mp4';
        Storage::disk('local')->makeDirectory($directory);
        $output = fopen(Storage::disk('local')->path($finalPath), 'wb');
        abort_unless($output, 500, 'Không thể tạo file video tạm.');

        try {
            for ($index = 0; $index < $data['total_chunks']; $index++) {
                $chunkPath = $directory . '/chunks/' . $index . '.part';
                abort_unless(Storage::disk('local')->exists($chunkPath), 422, 'Thiếu phần video số ' . ($index + 1) . '.');
                $input = fopen(Storage::disk('local')->path($chunkPath), 'rb');
                abort_unless($input, 500, 'Không thể đọc phần video.');
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
        } finally {
            fflush($output);
            fclose($output);
        }

        // Trên Windows, PHP có thể giữ stat cache từ lúc file vừa được tạo với
        // dung lượng 0 byte. Xóa cache sau khi đóng stream trước khi kiểm tra.
        $absoluteFinalPath = Storage::disk('local')->path($finalPath);
        clearstatcache(true, $absoluteFinalPath);
        $actualSize = filesize($absoluteFinalPath);

        $expectedSize = (int) $data['expected_size'];

        if ($actualSize === false || $actualSize !== $expectedSize) {
            Storage::disk('local')->delete($finalPath);
            abort(422, sprintf(
                'Dung lượng video sau khi ghép không khớp (nhận %s byte, dự kiến %s byte).',
                $actualSize === false ? 'không xác định' : number_format($actualSize, 0, ',', '.'),
                number_format($expectedSize, 0, ',', '.')
            ));
        }
        Storage::disk('local')->deleteDirectory($directory . '/chunks');

        return response()->json(['upload_token' => $data['upload_id']]);
    }

    private function directory(Request $request, string $uploadId): string
    {
        return 'tmp/video-uploads/' . $request->user()->id . '/' . $uploadId;
    }
}
