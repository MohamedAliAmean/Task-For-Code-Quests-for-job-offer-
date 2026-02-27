<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'position',
        'title',
        'description',
        'video_url',
        'is_preview',
        'is_required',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
            'is_required' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function getVideoProviderAttribute(): string
    {
        $url = self::normalizeVideoUrl($this->video_url);

        if ($url && self::extractYoutubeId($url)) {
            return 'youtube';
        }

        if ($url && self::extractVimeoId($url)) {
            return 'vimeo';
        }

        return 'html5';
    }

    public function getVideoEmbedIdAttribute(): ?string
    {
        $url = self::normalizeVideoUrl($this->video_url);

        if (! $url) {
            return null;
        }

        return self::extractYoutubeId($url) ?? self::extractVimeoId($url);
    }

    public function getVideoPlaybackUrlAttribute(): ?string
    {
        $url = self::normalizeVideoUrl($this->video_url);

        if (! $url) {
            return null;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $path = ltrim($url, '/');

        return "/storage/{$path}";
    }

    public static function normalizeVideoUrl(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (Str::contains($input, '<iframe', ignoreCase: true)) {
            $src = self::extractIframeSrc($input);

            return $src ?: null;
        }

        return $input;
    }

    private static function extractIframeSrc(string $html): ?string
    {
        if (preg_match('/\ssrc=(["\'])(?<src>.*?)\1/i', $html, $matches) !== 1) {
            return null;
        }

        $src = trim((string) ($matches['src'] ?? ''));

        return $src !== '' ? $src : null;
    }

    private static function extractYoutubeId(string $url): ?string
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return null;
        }

        $host = Str::lower((string) ($parts['host'] ?? ''));
        $host = Str::replaceStart('www.', '', $host);
        $path = (string) ($parts['path'] ?? '');
        $query = (string) ($parts['query'] ?? '');

        if ($host === 'youtu.be') {
            $candidate = trim($path, '/');
            $candidate = explode('/', $candidate)[0] ?? '';

            return $candidate !== '' ? $candidate : null;
        }

        $allowedHosts = [
            'youtube.com',
            'm.youtube.com',
            'music.youtube.com',
            'youtube-nocookie.com',
        ];

        if (! in_array($host, $allowedHosts, true)) {
            return null;
        }

        parse_str($query, $params);

        if (isset($params['v']) && is_string($params['v']) && $params['v'] !== '') {
            return $params['v'];
        }

        foreach (['/embed/', '/shorts/', '/live/'] as $prefix) {
            if (! Str::contains($path, $prefix)) {
                continue;
            }

            $candidate = Str::after($path, $prefix);
            $candidate = explode('/', $candidate)[0] ?? '';

            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private static function extractVimeoId(string $url): ?string
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return null;
        }

        $host = Str::lower((string) ($parts['host'] ?? ''));
        $host = Str::replaceStart('www.', '', $host);
        $path = (string) ($parts['path'] ?? '');

        if ($host === 'vimeo.com') {
            $candidate = trim($path, '/');
            $candidate = explode('/', $candidate)[0] ?? '';

            return ctype_digit($candidate) ? $candidate : null;
        }

        if ($host === 'player.vimeo.com') {
            if (! Str::startsWith($path, '/video/')) {
                return null;
            }

            $candidate = Str::after($path, '/video/');
            $candidate = explode('/', $candidate)[0] ?? '';

            return ctype_digit($candidate) ? $candidate : null;
        }

        return null;
    }
}
