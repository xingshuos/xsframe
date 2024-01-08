<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\util;

class VideoUtil
{
    // 视频转换
    public static function videoTransform($fileRootPath, $filePath, $filenamePrefix, $type, $sizeStr = '640x360')
    {
        $mp4Path     = "{$filePath}/{$filenamePrefix}.mp4";
        $keyinfoPath = "{$fileRootPath}/enc.keyinfo";
        $commandStr  = ' ffmpeg -y -threads 10 -i "%s" -profile:v baseline -level 3.0 -s ' . $sizeStr . ' -start_number 0 -hls_time 10 -hls_key_info_file ' . $keyinfoPath . ' -hls_list_size 0 -f hls ' . "{$filePath}/{$type}/{$filenamePrefix}.m3u8" . ' 2>&1 ';
        $command     = sprintf($commandStr, $mp4Path);
        exec($command);
        return true;
    }

    // 获取视频基本信息-
    public static function getVideoInfo($file)
    {
        // $commandStr = ' ffmpeg -i "%s" 2>&1 ';
        $commandStr = ' ffprobe -show_data -hide_banner "%s"  2>&1 ';
        $command    = sprintf($commandStr, $file);
        ob_start();
        passthru($command);
        $info = ob_get_contents();
        ob_end_clean();
        $data = array();
        if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
            $data['duration'] = $match[1]; //播放时间
            $arr_duration     = explode(':', $match[1]);
            $data['seconds']  = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
            $data['start']    = $match[2]; //开始时间
            $data['bitrate']  = $match[3]; //码率(kb)
        }

        if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
            $data['vcodec']     = $match[1]; //视频编码格式
            $data['vformat']    = $match[2]; //视频格式
            $data['resolution'] = $match[3]; //视频分辨率
            $arr_resolution     = explode('x', $match[3]);
            $data['width']      = $arr_resolution[0];
            $data['height']     = $arr_resolution[1];
        }

        if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
            $data['acodec']      = $match[1]; //音频编码
            $data['asamplerate'] = $match[2]; //音频采样频率
        }

        if (isset($data['seconds']) && isset($data['start'])) {
            $data['play_time'] = $data['seconds'] + $data['start']; //实际播放时间
        }
        $data['size'] = filesize($file); //文件大小
        return $data;
    }
}