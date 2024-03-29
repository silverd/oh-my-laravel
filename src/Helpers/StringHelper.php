<?php

namespace Silverd\OhMyLaravel\Helpers;

class StringHelper
{
    /**
     * 产生随机字符
     *
     * @param int $length
     * @param bool $numeric 是否为纯数字
     *
     * @return string
     */
    public static function randCode(int $length, bool $numeric = false, string $sourceStr = '')
    {
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        }
        else {
            $hash  = '';
            $chars = $sourceStr ? $sourceStr : '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
            $max   = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }

        return $hash;
    }

    // 随机获取一个姓氏
    public static function randSurname()
    {
        $surnames = [
            '赵', '钱', '孙', '李', '周', '吴', '郑', '王', '冯', '陈', '褚', '卫', '蒋', '沈', '韩', '杨', '朱', '秦',
            '尤', '许', '何', '吕', '施', '张', '孔', '曹', '严', '华', '金', '魏', '陶', '姜', '戚', '谢', '邹', '喻',
            '柏', '水', '窦', '章', '云', '苏', '潘', '葛', '奚', '范', '彭', '郎', '鲁', '韦', '昌', '马', '苗', '凤',
            '花', '方', '俞', '任', '袁', '柳', '酆', '鲍', '史', '唐', '费', '廉', '岑', '薛', '雷', '贺', '倪', '汤',
            '滕', '殷', '罗', '毕', '郝', '邬', '安', '常', '乐', '于', '时', '傅', '皮', '卞', '齐', '康', '伍', '余',
            '元', '卜', '顾', '孟', '平', '黄', '和', '穆', '萧', '尹', '姚', '邵', '湛', '汪', '祁', '毛', '禹', '狄',
            '米', '贝', '明', '臧', '计', '伏', '成', '戴', '谈', '宋', '茅', '庞', '熊', '纪', '舒', '屈', '项', '祝',
            '董', '梁', '杜', '阮', '蓝', '闵', '席', '季', '麻', '强', '贾', '路', '娄', '危', '江', '童', '颜', '郭',
            '梅', '盛', '林', '刁', '钟', '徐', '邱', '骆', '高', '夏', '蔡', '田', '樊', '胡', '凌', '霍', '虞', '万',
            '支', '柯', '昝', '管', '卢', '莫', '经', '房', '裘', '缪', '干', '解', '应', '宗', '丁', '宣', '贲', '邓',
            '郁', '单', '杭', '洪', '包', '诸', '左', '石', '崔', '吉', '钮', '龚', '程', '嵇', '邢', '滑', '裴', '陆',
            '荣', '翁', '荀', '羊', '于', '惠', '甄', '曲', '家', '封', '芮', '羿', '储', '靳', '汲', '邴', '糜', '松',
            '井', '段', '富', '巫', '乌', '焦', '巴', '弓', '牧', '隗', '山', '谷', '车', '侯', '宓', '蓬', '全', '郗',
            '班', '仰', '秋', '仲', '伊', '宫', '宁', '仇', '栾', '暴', '甘', '钭', '厉', '戎', '祖', '武', '符', '刘',
            '景', '詹', '束', '龙', '叶', '幸', '司', '韶', '郜', '黎', '蓟', '溥', '印', '宿', '白', '怀', '蒲', '邰',
            '从', '鄂', '索', '咸', '籍', '赖', '卓', '蔺', '屠', '蒙', '池', '乔', '阴', '郁', '胥', '能', '苍', '双',
            '闻', '莘', '党', '翟', '谭', '贡', '劳', '逄', '姬', '申', '扶', '堵', '冉', '宰', '郦', '雍', '却', '璩',
            '桑', '桂', '濮', '牛', '寿', '通', '边', '扈', '燕', '冀', '浦', '尚', '农', '温', '别', '庄', '晏', '柴',
            '瞿', '阎', '充', '慕', '连', '茹', '习', '宦', '艾', '鱼', '容', '向', '古', '易', '慎', '戈', '廖', '庾',
            '终', '暨', '居', '衡', '步', '都', '耿', '满', '弘', '匡', '国', '文', '寇', '广', '禄', '阙', '东', '欧',
            '殳', '沃', '利', '蔚', '越', '夔', '隆', '师', '巩', '厍', '聂', '晁', '勾', '敖', '融', '冷', '訾', '辛',
            '阚', '那', '简', '饶', '空', '曾', '毋', '沙', '乜', '养', '鞠', '须', '丰', '巢', '关', '蒯', '相', '查',
            '后', '荆', '红', '游', '郏', '竺', '权', '逯', '盖', '益', '桓', '公', '仉', '督', '岳', '帅', '缑', '亢',
            '况', '郈', '有', '琴', '归', '海', '晋', '楚', '闫', '法', '汝', '鄢', '涂', '钦', '商', '牟', '佘', '佴',
            '伯', '赏', '墨', '哈', '谯', '篁', '年', '爱', '阳', '佟', '言', '福', '南', '火', '铁', '迟', '漆', '官',
            '冼', '真', '展', '繁', '檀', '祭', '密', '敬', '揭', '舜', '楼', '疏', '冒', '浑', '挚', '胶', '随', '高',
            '皋', '原', '种', '练', '弥', '仓', '眭', '蹇', '覃', '阿', '门', '恽', '来', '綦', '召', '仪', '风', '介',
            '巨', '木', '京', '狐', '郇', '虎', '枚', '抗', '达', '杞', '苌', '折', '麦', '庆', '过', '竹', '端', '鲜',
            '皇', '亓', '老', '是', '秘', '畅', '邝', '还', '宾', '闾', '辜', '纵',
        ];

        return \Arr::random($surnames);
    }

    public static function getTimeCountDown(string $dateTime)
    {
        $val = max(time() - strtotime($dateTime), 1);

        if ($val < 60) {
            return $val . '秒前';
        }
        elseif ($val < 3600) {
            return intval($val / 60) . '分钟前';
        }
        elseif ($val < 86400) {
            return intval($val / 3600) . '小时前';
        }
        else {
            return $dateTime;
        }
    }

    // 字符串脱敏
    public static function desensitize(string $str, int $head = 3, int $tail = 3)
    {
        $strLen = mb_strlen($str);

        // 隐藏字符串长度
        $hideLen = $strLen - ($head + $tail);

        $newStr = mb_substr($str, 0, $head, 'UTF-8')
            . str_pad('', $hideLen, '*')
            . mb_substr($str, ($strLen - $tail), null, 'UTF-8');

        return $newStr;
    }

    // 提取干净的 JSON 字符串
    public static function extractJson(string $str)
    {
        $regex = <<<EOT
            /(\{(?:(?>[^{}"'\/]+)|(?>"(?:(?>[^\\"]+)|\\.)*")|(?>'(?:(?>[^\\']+)|\\.)*')|(?>\/\/.*\n)|(?>\/\*.*?\*\/)|(?-1))*\})/
        EOT;

        $str = str_replace(['\n' | '\r'], PHP_EOL, $str);

        // 不含 JSON
        if (! preg_match($regex, $str, $matches)) {
            return false;
        }

        $str = $matches[0] ?? '';

        return json_decode($str, true);
    }
}
