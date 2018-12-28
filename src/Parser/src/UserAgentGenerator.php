<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser;

use InvalidArgumentException;

/**
 * Generate hundreds of thousands of unique mobile & desktop User Agents that are 100% authentic.
 * Supports Hundreds:
 *  - Android devices,
 *  - 32 & 64 bit versions of Windows XP-10.5,
 *  - Linux 540-686
 *  - Mac 7-10.12
 *  - Firefox
 *  - Chrome
 *  - Internet Explorer.
 *
 * Class UserAgent
 * @package Parser
 */
class UserAgentGenerator
{
    /**
     * Windows Operating System list with dynamic versioning
     * @var array $windowsOS
     */
    protected $windowsOS = [
        '[Windows; |Windows; U; |]Windows NT 6.:number0-3:;[ Win64; x64| WOW64| x64|]',
        '[Windows; |Windows; U; |]Windows NT 10.:number0-5:;[ Win64; x64| WOW64| x64|]',
    ];

    /**
     * Linux Operating Systems [limited]
     * @var array $linuxOS
     */
    protected $linuxOS = [
        '[Linux; |][U; |]Linux x86_64',
        '[Linux; |][U; |]Linux i:number5-6::number4-8::number0-6: [x86_64|]',
    ];

    /**
     * Mac Operating System (OS X) with dynamic versioning
     * @var array $macOS
     */
    protected $macOS = [
        'Macintosh; [U; |]Intel Mac OS X :number7-9:_:number0-9:_:number0-9:',
        'Macintosh; [U; |]Intel Mac OS X 10_:number0-12:_:number0-9:',
    ];

    /**
     * Versions of Android to be used
     * @var array $androidVersions
     */
    protected $androidVersions = [
        '4.3.1',
        '4.4',
        '4.4.1',
        '4.4.4',
        '5.0',
        '5.0.1',
        '5.0.2',
        '5.1',
        '5.1.1',
        '6.0',
        '6.0.1',
        '7.0',
        '7.1',
        '7.1.1',
    ];

    /**
     * Holds the version of android for the User Agent being generated
     * @property string $androidVersion
     */
    protected $androidVersion;

    /**
     * Android devices and for specific android versions
     * @var array $androidDevices
     */
    protected $androidDevices = [
        '4.3' => [
            'GT-I9:number2-5:00 Build/JDQ39',
            'Nokia 3:number1-3:[10|15] Build/IMM76D',
            '[SAMSUNG |]SM-G3:number1-5:0[R5|I|V|A|T|S] Build/JLS36C',
            'Ascend G3:number0-3:0 Build/JLS36I',
            '[SAMSUNG |]SM-G3:number3-6::number1-8::number0-9:[V|A|T|S|I|R5] Build/JLS36C',
            'HUAWEpublicI G6-L:number10-11: Build/HuaweiG6-L:number10-11:',
            '[SAMSUNG |]SM-[G|N]:number7-9:1:number0-8:[S|A|V|T] Build/[JLS36C|JSS15J]',
            '[SAMSUNG |]SGH-N0:number6-9:5[T|V|A|S] Build/JSS15J',
            'Samsung Galaxy S[4|IV] Mega GT-I:number89-95:00 Build/JDQ39',
            'SAMSUNG SM-T:number24-28:5[s|a|t|v] Build/[JLS36C|JSS15J]',
            'HP :number63-73:5 Notebook PC Build/[JLS36C|JSS15J]',
            'HP Compaq 2:number1-3:10b Build/[JLS36C|JSS15J]',
            'HTC One 801[s|e] Build/[JLS36C|JSS15J]',
            'HTC One max Build/[JLS36C|JSS15J]',
            'HTC Xplorer A:number28-34:0[e|s] Build/GRJ90',
        ],
        '4.4' => [
            'XT10:number5-8:0 Build/SU6-7.3',
            'XT10:number12-52: Build/[KXB20.9|KXC21.5]',
            'Nokia :number30-34:10 Build/IMM76D',
            'E:number:20-23::number0-3::number0-4: Build/24.0.[A|B].1.34',
            '[SAMSUNG |]SM-E500[F|L] Build/KTU84P',
            'LG Optimus G Build/KRT16M',
            'LG-E98:number7-9: Build/KOT49I',
            'Elephone P:number2-6:000 Build/KTU84P',
            'IQ450:number0-4: Quad Build/KOT49H',
            'LG-F:number2-5:00[K|S|L] Build/KOT49[I|H]',
            'LG-V:number3-7::number0-1:0 Build/KOT49I',
            '[SAMSUNG |]SM-J:number1-2::number0-1:0[G|F] Build/KTU84P',
            '[SAMSUNG |]SM-N80:number0-1:0 Build/[KVT49L|JZO54K]',
            '[SAMSUNG |]SM-N900:number5-8: Build/KOT49H',
            '[SAMSUNG-|]SGH-I337[|M] Build/[JSS15J|KOT49H]',
            '[SAMSUNG |]SM-G900[W8|9D|FD|H|V|FG|A|T] Build/KOT49H',
            '[SAMSUNG |]SM-T5:number30-35: Build/[KOT49H|KTU84P]',
            '[Google |]Nexus :number5-7: Build/KOT49H',
            'LG-H2:number0-2:0 Build/KOT49[I|H]',
            'HTC One[_M8|_M9|0P6B|801e|809d|0P8B2|mini 2|S][ dual sim|] Build/[KOT49H|KTU84L]',
            '[SAMSUNG |]GT-I9:number3-5:0:number0-6:[V|I|T|N] Build/KOT49H',
            'Lenovo P7:number7-8::number1-6: Build/[Lenovo|JRO03C]',
            'LG-D95:number1-8: Build/KOT49[I|H]',
            'LG-D:number1-8::number0-8:0 Build/KOT49[I|H]',
            'Nexus5 V:number6-7:.1 Build/KOT49H',
            'Nexus[_|] :number4-10: Build/[KOT49H|KTU84P]',
            'Nexus[_S_| S ][4G |]Build/GRJ22',
            '[HM NOTE|NOTE-III|NOTE2 1LTE[TD|W|T]',
            'ALCATEL ONE[| ]TOUCH 70:number2-4::number0-9:[X|D|E|A] Build/KOT49H',
            'MOTOROLA [MOTOG|MSM8960|RAZR] Build/KVT49L',
        ],
        '5.0' => [
            'Nokia :number10-11:00 [wifi|4G|LTE] Build/GRK39F',
            'HTC 80:number1-2[s|w|e|t] Build/[LRX22G|JSS15J]',
            'Lenovo A7000-a Build/LRX21M;',
            'HTC Butterfly S [901|919][s|d|] Build/LRX22G',
            'HTC [M8|M9|M8 Pro Build/LRX22G',
            'LG-D3:number25-37: Build/LRX22G',
            'LG-D72:number0-9: Build/LRX22G',
            '[SAMSUNG |]SM-G4:number0-9:0 Build/LRX22[G|C]',
            '[|SAMSUNG ]SM-G9[00|25|20][FD|8|F|F-ORANGE|FG|FQ|H|I|L|M|S|T] Build/[LRX21T|KTU84F|KOT49H]',
            '[SAMSUNG |]SM-A:number7-8:00[F|I|T|H|] Build/[LRX22G|LMY47X]',
            '[SAMSUNG-|]SM-N91[0|5][A|V|F|G|FY] Build/LRX22C',
            '[SAMSUNG |]SM-[T|P][350|550|555|355|805|800|710|810|815] Build/LRX22G',
            'LG-D7:number0-2::number0-9: Build/LRX22G',
            '[LG|SM]-[D|G]:number8-9::number0-5::number0-9:[|P|K|T|I|F|T1] Build/[LRX22G|KOT49I|KVT49L|LMY47X]',
        ],
        '5.1' => [
            'Nexus :number5-9: Build/[LMY48B|LRX22C]',
            '[|SAMSUNG ]SM-G9[28|25|20][X|FD|8|F|F-ORANGE|FG|FQ|H|I|L|M|S|T] Build/[LRX22G|LMY47X]',
            '[|SAMSUNG ]SM-G9[35|350][X|FD|8|F|F-ORANGE|FG|FQ|H|I|L|M|S|T] Build/[MMB29M|LMY47X]',
            '[MOTOROLA |][MOTO G|MOTO G XT1068|XT1021|MOTO E XT1021|MOTO XT1580|MOTO X FORCE XT1580|'
            . 'MOTO X PLAY XT1562|MOTO XT1562|MOTO XT1575|MOTO X PURE XT1575|MOTO XT1570 MOTO X STYLE]'
            . ' Build/[LXB22|LMY47Z|LPC23|LPK23|LPD23|LPH223]',
        ],
        '6.0' => [
            '[SAMSUNG |]SM-[G|D][920|925|928|9350][V|F|I|L|M|S|8|I] Build/[MMB29K|MMB29V|MDB08I|MDB08L]',
            'Nexus :number5-7:[P|X|] Build/[MMB29K|MMB29V|MDB08I|MDB08L]',
            'HTC One[_| ][M9|M8|M8 Pro] Build/MRA58K',
            'HTC One[_M8|_M9|0P6B|801e|809d|0P8B2|mini 2|S][ dual sim|] Build/MRA58K',
        ],
        '7.0' => [
            'Pixel [XL|C] Build/[NRD90M|NME91E]',
            'Nexus :number5-9:[X|P|] Build/[NPD90G|NME91E]',
            '[SAMSUNG |]GT-I:number91-98:00 Build/KTU84P',
            'Xperia [V |]Build/NDE63X',
            'LG-H:number90-93:0 Build/NRD90[C|M]',
        ],
        '7.1' => [
            'Pixel [XL|C] Build/[NRD90M|NME91E]',
            'Nexus :number5-9:[X|P|] Build/[NPD90G|NME91E]',
            '[SAMSUNG |]GT-I:number91-98:00 Build/KTU84P',
            'Xperia [V |]Build/NDE63X',
            'LG-H:number90-93:0 Build/NRD90[C|M]',
        ],
    ];

    /**
     * Lpublicist of "OS" strings used for android
     * @var array $androidOS
     */
    protected $androidOS = [
        'Linux; Android :androidVersion:; :androidDevice:',
        //TODO: Add a $windowsDevices variable that does the same as androidDevice
        //'Windows Phone 10.0; Android :androidVersion:; :windowsDevice:',
        'Linux; U; Android :androidVersion:; :androidDevice:',
        'Android; Android :androidVersion:; :androidDevice:',
    ];

    /**
     * List of "OS" strings used for iOS
     * @var array $mobileIOS
     */
    protected $mobileIOS = [
        'iphone' => 'iPhone; CPU iPhone OS :number7-11:_:number0-9:_:number0-9:; like Mac OS X;',
        'ipad' => 'iPad; CPU iPad OS :number7-11:_:number0-9:_:number0-9: like Mac OS X;',
        'ipod' => 'iPod; CPU iPod OS :number7-11:_:number0-9:_:number0-9:; like Mac OS X;',
    ];

    /**
     * @param null $os
     * @return string|string[]|null
     */
    protected function getOS($os = null)
    {
        $_os = [];
        if ($os === null || in_array($os, ['chrome', 'firefox', 'explorer'])) {
            $_os = $os === 'explorer' ? $this->windowsOS : array_merge($this->windowsOS, $this->linuxOS, $this->macOS);
        } else {
            $_os += $this->{$os . 'OS'};
        }
        // randomly select on operating system
        $selectedOS = rtrim($_os[rand(0, count($_os) - 1)], ';');

        // check for spin syntax
        if (strpos($selectedOS, '[') !== false) {
            $selectedOS = self::processSpinSyntax($selectedOS);
        }

        // check for random number syntax
        if (strpos($selectedOS, ':number') !== false) {
            $selectedOS = self::processRandomNumbers($selectedOS);
        }

        if (rand(1, 100) > 50) {
            $selectedOS .= '; en-US';
        }

        return $selectedOS;
    }

    /**
     * @param null $telephone
     * @return string|string[]|null
     */
    protected function getMobileOS($telephone = null)
    {
        $telephone = strtolower($telephone);
        $systems = [];
        switch ($telephone) {
            case 'android':
                $systems += $this->androidOS;
                break;
            case 'iphone':
            case 'ipad':
            case 'ipod':
                $systems[] = array_values($this->mobileIOS[$telephone]);
                break;
            default:
                $systems = array_merge($this->androidOS, $this->mobileIOS);
        }
        // select random mobile os
        $selectedOS = rtrim($systems[rand(0, count($systems) - 1)], ';');
        if (strpos($selectedOS, ':androidVersion:') !== false) {
            $selectedOS = $this->processAndroidVersion($selectedOS);
        }
        if (strpos($selectedOS, ':androidDevice:') !== false) {
            $selectedOS = $this->addAndroidDevice($selectedOS);
        }
        if (strpos($selectedOS, ':number') !== false) {
            $selectedOS = self::processRandomNumbers($selectedOS);
        }

        return $selectedOS;
    }

    /**
     * @param $selected_os
     * @return string|string[]|null
     */
    protected static function processRandomNumbers($selected_os)
    {
        return preg_replace_callback('/:number(\d+)-(\d+):/i', function ($matches) {
            return rand($matches[1], $matches[2]);
        }, $selected_os);
    }

    /**
     * @param $selected_os
     * @return string|string[]|null
     */
    protected static function processSpinSyntax($selected_os)
    {
        return preg_replace_callback('/\[([\w\-\s|;]*?)\]/i', function ($matches) {
            $shuffle = explode('|', $matches[1]);

            return $shuffle[array_rand($shuffle)];
        }, $selected_os);
    }

    /**
     * @param $os
     * @return string|string[]|null
     */
    protected function processAndroidVersion($os)
    {
        $this->androidVersion = $version = $this->androidVersions[array_rand($this->androidVersions)];

        return preg_replace_callback('/:androidVersion:/i', function ($matches) use ($version) {
            return $version;
        }, $os);
    }

    /**
     * @param $selected_os
     * @return string|string[]|null
     */
    protected function addAndroidDevice($selected_os)
    {
        $devices = $this->androidDevices[substr($this->androidVersion, 0, 3)];
        $device = $devices[array_rand($devices)];

        $device = self::processSpinSyntax($device);

        return preg_replace_callback('/:androidDevice:/i', function ($matches) use ($device) {
            return $device;
        }, $selected_os);
    }

    /**
     * @param $version
     * @return int
     */
    protected static function getRandomVersion($version)
    {
        return rand($version['min'], $version['max']);
    }

    /**
     * @param $version
     * @return string
     */
    protected static function chromeVersion($version)
    {
        return self::getRandomVersion($version) . '.0.' . rand(1000, 4000) . '.' . rand(100, 400);
    }

    /**
     * @param $version
     * @return string
     */
    protected static function firefoxVersion($version)
    {
        return self::getRandomVersion($version) . '.' . rand(0, 9);
    }

    /**
     * @param $version
     * @return string
     */
    protected static function windows($version)
    {
        return self::getRandomVersion($version) . '.' . rand(0, 9);
    }

    /**
     * @param null $os
     * @return string
     */
    public function generate($os = null)
    {
        $agent = $this->createRandomAgent($os);
        $_SESSION['agent'] = $agent;

        switch ($agent) {
            case 'chrome':
                return $this->createRandomChromeUserAgent($agent);
            case 'firefox':
                $os = $this->getOS($agent);

                return $this->createRandomFirefoxUserAgent($os);
            case 'explorer':
                return $this->createRandomExplorerUserAgent();
            case 'mobile':
            case 'android':
            case 'iphone':
            case 'ipad':
            case 'ipod':
                $os = $this->getMobileOS($agent);

                return $this->createRandomMobileUserAgent($os);
        }

        throw new InvalidArgumentException("Undefined os '$os'");
    }

    /**
     * @param $os
     * @return mixed
     */
    protected function createRandomAgent($os)
    {
        if ($os === null) {
            if (rand(0, 100) >= 44) {
                $os = array_rand(array_flip(['firefox', 'chrome', 'explorer']));
            } else {
                $os = array_rand(array_flip(['iphone', 'android', 'mobile']));
            }
        } elseif ($os == 'windows' || $os == 'mac' || $os == 'linux') {
            $agents = ['firefox', 'chrome'];

            if ($os == 'windows') {
                $agents[] = 'explorer';
            }

            $os = array_rand(array_flip($agents));
        }

        return $os;
    }

    /**
     * @param $device
     * @return string
     */
    public function createRandomChromeUserAgent($device)
    {
        $chromeVersion = self::chromeVersion(['min' => 47, 'max' => 55]);

        return "Mozilla/5.0 ({$this->getOS($device)}) AppleWebKit/{$this->createRandomAppleWebkitVersion()}"
            . " (KHTML, like Gecko) Chrome/{$chromeVersion} Safari/{$this->createRandomSafariVersion()}";
    }

    /**
     * @param $os
     * @return string
     */
    public function createRandomFirefoxUserAgent($os)
    {
        $firefoxVersion = self::firefoxVersion(['min' => 45, 'max' => 74]);
        $geckoVersion = rand(1, 100) > 30 ? '20100101' : '20130401';

        return "Mozilla/5.0 ({$os}) Gecko/{$geckoVersion} Firefox/{$firefoxVersion}";
    }

    /**
     * @return string
     */
    public function createRandomExplorerUserAgent()
    {
        $msieVersion = rand(7, 11);

        switch ($msieVersion) {
            case 7:
            case 8:
                $trident = '4';
                break;
            case 9:
                $trident = '5';
                break;
            case 10:
                $trident = '6';
                break;

            default:
                $trident = '7';
                break;
        }

        return "Mozilla/5.0 (compatible; MSIE {$msieVersion}.0; {$this->getOS('windows')} Trident/{$trident}.0)";
    }

    /**
     * @param $device
     * @return string
     */
    public function createRandomMobileUserAgent($device)
    {
        $chromeFunction = self::chromeVersion(['min' => 47, 'max' => 55]);
        $mobileSafariVersion = $this->createRandomSafariVersion() . '.' . rand(0, 9);

        return "Mozilla/5.0 ({$device}) AppleWebKit/{$this->createRandomAppleWebkitVersion()}"
            . " (KHTML, like Gecko)  Chrome/{$chromeFunction} Mobile Safari/{$mobileSafariVersion}";
    }

    /**
     * @return int|string
     */
    protected function createRandomAppleWebkitVersion()
    {
        return rand(1, 100) > 50 ? rand(533, 537) : rand(600, 603) . '.' . rand(1, 50);
    }

    /**
     * @return int
     */
    protected function createRandomSafariVersion()
    {
        return rand(1, 100) > 50 ? rand(533, 537) : rand(600, 603);
    }
}
