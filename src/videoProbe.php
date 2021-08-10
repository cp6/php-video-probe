<?php

use vpException as ve;

class videoProbe
{
    public string $output_file;//Saved json output filename from ffprobe
    public array $output = [];//Saved json output file loaded as array

    public string $filename;//videoname.mkv
    public string $file_ext;//mkv

    //Video
    public string|null $video_codec;
    public string|null $video_codec_short;
    public string|null $pix_fmt;
    public string|null $framerate;
    public string|null $duration;
    public string|null $start_time;
    public float|null $bitrate;
    public int|null $size;
    public string|null $aspect_ratio;
    public string|null $format_long_name;
    public string|null $profile;
    public int|null $streams;
    public int|null $programs;
    public int|null $width;
    public int|null $height;
    public int|null $frames;
    public int|null $bits_per_raw_sample;
    public int|null $level;
    public array $video_streams = [];

    //Audio
    public string|null $audio_codec;
    public string|null $audio_codec_short;
    public string|null $audio_codec_tag;
    public string|null $audio_profile;
    public int|null $audio_channels;
    public int|null $audio_bitrate;
    public int|null $audio_duration;
    public string|null $audio_start_time;
    public string|null $audio_channel_layout;
    public string|null $audio_sample_rate;
    public array $audio_streams = [];

    //Subtitles
    public array $subtitles = [];//Available subtitle languages

    //General
    public bool $has_audio = false;//Has an audio stream
    public bool $has_subtitle = false;//Has subtitles
    public bool $has_thumbnail = false;//Has thumbnail embedded
    public string $thumbnail;//Thumbnail filename

    public string|null $encoder;
    public string|null $title;
    public string|null $creation_date;
    public string|null $date_released;
    public string|null $comment;

    public array $formats = [];//Formats names/tags into an array

    public function ffprobeInstalled(): bool
    {//Checks for ffprobe/ffmpeg installed Install with: sudo apt install ffmpeg
        if (empty(trim(shell_exec('type -P ffmpeg')))) {
            return false;
        }
        return true;
    }

    public function createOutputFile(string $video_name, string $output_save_as = 'ffprobe_out.json'): void
    {
        shell_exec("ffprobe -v quiet -print_format json -show_format -show_streams $video_name > $output_save_as");
    }

    public function setOutputFile(string $file): void
    {
        $this->output_file = $file;
    }

    public function setOutput(): void
    {
        try {
            if (file_exists($this->output_file) && $this->validateJson()) {
                $this->output = json_decode(file_get_contents($this->output_file), true);
            } else {
                throw new vpException("File: {$this->output_file} can not be found");
            }
        } catch (vpException $e) {//display error message
            echo $e->errorMessage();
        }
    }

    public function validateJson(): bool
    {
        try {
            json_decode(file_get_contents($this->output_file));
            if (json_last_error() === JSON_ERROR_NONE) {
                return true;
            }
            throw new vpException("File: {$this->output_file} is not valid json");
        } catch (vpException $e) {//display error message
            echo $e->errorMessage();
        }
        return false;
    }

    public function assignProperties(): void
    {
        if (isset($this->output['streams'][0])) {
            if (isset($this->output['format'])) {
                $f = $this->output['format'];
                $this->filename = $f['filename'];
                $this->file_ext = pathinfo($f['filename'], PATHINFO_EXTENSION);
                $this->streams = $f['nb_streams'];
                $this->programs = $f['nb_programs'];
                $this->format_long_name = $f['format_long_name'];
                $this->start_time = $f['start_time'];
                $this->duration = $f['duration'];
                $this->size = (int)$f['size'];
                $this->bitrate = (float)$f['bit_rate'];
                $this->formats = explode(",", $f['format_name']);
                if (isset($f['tags'])) {
                    $this->encoder = $f['tags']['encoder'] ?? null;
                    $this->date_released = $f['tags']['DATE_RELEASED'] ?? null;
                    $this->comment = $f['tags']['COMMENT'] ?? null;
                    $this->title = $f['tags']['TITLE'] ?? null;
                    if (isset($f['tags']['creation_time'])) {
                        $created_dt = \DateTime::createFromFormat('Y-m-d H:i:s', str_replace(["T", ".000000Z"], [" ", ""], $f['tags']['creation_time']));
                        $this->creation_date = $created_dt->format('Y-m-d H:i:s');
                    } else {
                        $this->creation_date = null;
                    }
                }
            }
            foreach ($this->output['streams'] as $d) {
                if (($d['tags']['mimetype'] ?? null) === 'image/jpeg') {
                    $this->has_thumbnail = true;
                    $this->thumbnail = $d['tags']['filename'];
                }
                if ($d['codec_type'] === 'video' && ($d['tags']['mimetype'] ?? null) !== 'image/jpeg') {
                    $this->video_codec = $d['codec_long_name'];
                    $this->video_codec_short = $d['codec_name'];
                    $this->height = $d['height'];
                    $this->width = $d['width'];
                    $this->aspect_ratio = $d['display_aspect_ratio'];
                    $this->framerate = explode("/", $d['avg_frame_rate'])[0];
                    $this->frames = $d['nb_frames'] ?? null;
                    $this->pix_fmt = $d['pix_fmt'];
                    $this->profile = $d['profile'];
                    $this->level = $d['level'];
                    $this->bits_per_raw_sample = $d['bits_per_raw_sample'];
                    $this->video_streams[] = array(
                        'index' => $d['index'],
                        'codec' => $d['codec_name'],
                        'codec_long_name' => $d['codec_long_name'],
                        'codec_time_base' => $d['codec_time_base'],
                        'profile' => $d['profile'],
                        'level' => $d['level'],
                        'pix_fmt' => $d['pix_fmt'],
                        'width' => $d['width'],
                        'height' => $d['height'],
                        'aspect_ratio' => $d['display_aspect_ratio'],
                        'bits' => (int)$d['bits_per_raw_sample'],
                        'start_time' => $d['start_time'],
                        'framerate' => explode("/", $d['avg_frame_rate'])[0]
                    );
                } elseif ($d['codec_type'] === 'audio') {
                    $this->has_audio = true;
                    $this->audio_codec = $d['codec_long_name'];
                    $this->audio_codec_short = $d['codec_name'];
                    $this->audio_codec_tag = $d['codec_tag_string'];
                    $this->audio_profile = $d['profile'];
                    $this->audio_sample_rate = $d['sample_rate'];
                    $this->audio_channels = $d['channels'];
                    $this->audio_bitrate = $d['bit_rate'] ?? null;
                    $this->audio_duration = $d['duration'] ?? null;
                    $this->audio_start_time = $d['start_time'];
                    $this->audio_channel_layout = $d['channel_layout'];
                    $this->audio_streams[] = array(
                        'index' => $d['index'],
                        'codec' => $d['codec_name'],
                        'codec_time_base' => $d['codec_time_base'],
                        'profile' => $d['profile'],
                        'channels' => $d['channels'],
                        'channel_layout' => $d['channel_layout'],
                        'start_time' => $d['start_time'],
                        'sample_rate' => $d['sample_rate'],
                        'title' => $d['tags']['language'] ?? null,
                        'language' => $d['tags']['title'] ?? null
                    );
                } elseif ($d['codec_type'] === 'subtitle') {
                    $this->has_subtitle = true;
                    if (isset($d['tags']['language'])) {
                        $this->subtitles[] = $d['tags']['language'];
                    }
                }
            }
        }
    }

    public function streamsAmount(): int
    {
        return $this->output['format']['nb_streams'];
    }

    public function bitrateAsKbps(bool $format = false, int $decimals = 2): float|string
    {
        if (is_null($this->bitrate)) {
            $result = $this->bitrate;
        } else {
            $result = ($this->output['format']['bit_rate'] / 1000);
        }
        if ($format) {
            return number_format($result, $decimals, ".", "");
        }
        return $result;
    }

    public function bitrateAsMbps(bool $format = false, int $decimals = 2): float
    {
        if (is_null($this->bitrate)) {
            $result = $this->bitrate;
        } else {
            $result = (($this->output['format']['bit_rate'] / 1000) / 1000);
        }
        if ($format) {
            return number_format($result, $decimals, ".", "");
        }
        return $result;
    }

    public function sizeAsMB(bool $format = false, int $decimals = 2): float
    {
        if (is_null($this->size)) {
            $result = $this->size;
        } else {
            $result = (($this->output['format']['size'] / 1024) / 1024);
        }
        if ($format) {
            return number_format($result, $decimals, ".", "");
        }
        return $result;
    }

    public function sizeAsGB(bool $format = false, int $decimals = 2): float
    {
        if (is_null($this->size)) {
            $result = $this->size;
        } else {
            $result = (($this->output['format']['size'] / 1024) / 1024 / 1024);
        }
        if ($format) {
            return number_format($result, $decimals, ".", "");
        }
        return $result;
    }

    public function widthHeightString(): string|null
    {
        try {
            if (isset($this->width, $this->height)) {
                return "{$this->width}x{$this->height}p";
            }
            throw new vpException("Properties not assigned. Please run assignProperties()");
        } catch (vpException $e) {//display error message
            echo $e->errorMessage();
        }
        return null;
    }

    public function durationAsString(): string
    {//Seconds to HH:MM:SS format
        $dur = ceil($this->output['format']['duration']);
        return sprintf("%02d%s%02d%s%02d", floor($dur / 3600), ":", ($dur / 60) % 60, ":", $dur % 60);
    }

    public function streamsAmountForType(string $type = 'audio'): int
    {
        $count = 0;
        foreach ($this->output['streams'] as $d) {
            if ($d['codec_type'] === $type) {
                $count++;
            }
        }
        return $count;
    }

    public function streamsOrder(): array
    {
        $arr = array();
        foreach ($this->output['streams'] as $d) {
            $arr[] = $d['codec_type'];
        }
        return $arr;
    }

    public function streamsTypes(): array
    {
        $arr = array();
        foreach ($this->output['streams'] as $d) {
            if (!in_array($d['codec_type'], $arr, true)) {
                $arr[] = $d['codec_type'];
            }
        }
        return $arr;
    }

    public function streamsTypesAmounts(): array
    {
        $video_count = $audio_count = $subtitle_count = 0;
        foreach ($this->output['streams'] as $d) {
            if ($d['codec_type'] === 'video') {
                $video_count++;
            } elseif ($d['codec_type'] === 'audio') {
                $audio_count++;
            } else {
                $subtitle_count++;
            }
        }
        return array('video' => $video_count, 'audio' => $audio_count, 'subtitle' => $subtitle_count);
    }

    public function mediaType(): int
    {
        $arr = $this->streamsTypes();
        if (in_array('video', $arr, true) && in_array('audio', $arr, true)) {
            return 1;//Video with audio
        } elseif (in_array('video', $arr, true) && !in_array('audio', $arr, true)) {
            return 2;//Video with NO audio
        } elseif (!in_array('video', $arr, true) && in_array('audio', $arr, true)) {
            return 3;//Audio with NO video
        } else {
            return 4;//Unknown
        }
    }

    public function mediaTypeAsString(int|null $type = null): string
    {
        if (is_null($type)) {
            $type = $this->mediaType();
        }
        if ($type === 1) {
            return 'Video with audio';
        } elseif ($type === 2) {
            return 'Video with NO audio';
        } elseif ($type === 3) {
            return 'Audio with NO video';
        } else {
            return 'Unknown';
        }
    }

    public function returnStreamArray(int $stream_index = 0): array
    {
        return $this->output['streams'][$stream_index] ?? [];
    }

}