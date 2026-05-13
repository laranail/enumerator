<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Illuminate\Support\Str;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum MimeTypeCategoryEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Text')] case Text = 'text';
    #[Label('Image')] case Image = 'image';
    #[Label('Video')] case Video = 'video';
    #[Label('Audio')] case Audio = 'audio';
    #[Label('Application')] case Application = 'application';
    #[Label('Font')] case Font = 'font';
    #[Label('Multipart')] case Multipart = 'multipart';
    #[Label('Message')] case Message = 'message';
    #[Label('Other')] case Other = 'other';

    public static function fromMime(string $mime): self
    {
        $primary = Str::lower(explode('/', $mime, 2)[0] ?? '');

        return match ($primary) {
            'text' => self::Text,
            'image' => self::Image,
            'video' => self::Video,
            'audio' => self::Audio,
            'application' => self::Application,
            'font' => self::Font,
            'multipart' => self::Multipart,
            'message' => self::Message,
            default => self::Other,
        };
    }
}
