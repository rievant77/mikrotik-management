<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrafficSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'mark_name',
        'address_list_name',
        'fqdn_domains',
        'tls_host',
        'tcp_ports',
        'udp_ports',
        'is_quic',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'fqdn_domains' => 'array',
        'is_quic' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Scope to only include active signatures.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Get the default platform signature definitions with updated Indonesian & global ports.
     */
    public static function getDefaultDefinitions(): array
    {
        return [
            // 1. Streaming Video
            [
                'name' => 'YouTube & Google Video',
                'category' => 'video',
                'mark_name' => 'agy_cm_youtube',
                'address_list_name' => 'agy_list_youtube',
                'fqdn_domains' => ['youtube.com', 'googlevideo.com', 'ytimg.com', 'youtu.be', 'ggpht.com', 'youtubei.googleapis.com'],
                'tls_host' => '*googlevideo.com,*youtube.com,*ytimg.com,*youtu.be,*ggpht.com,*gvt1.com,*youtubei.googleapis.com',
                'tcp_ports' => '80,443',
                'udp_ports' => '443',
                'is_quic' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'TikTok Video',
                'category' => 'video',
                'mark_name' => 'agy_cm_tiktok',
                'address_list_name' => 'agy_list_tiktok',
                'fqdn_domains' => ['tiktok.com', 'tiktokcdn.com', 'byteoversea.com', 'ibytedtos.com', 'musical.ly'],
                'tls_host' => '*tiktokcdn.com,*tiktokv.com,*byteoversea.com,*musical.ly,*tiktok.com,*ibytedtos.com',
                'tcp_ports' => '80,443',
                'udp_ports' => '443',
                'is_quic' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Netflix',
                'category' => 'video',
                'mark_name' => 'agy_cm_netflix',
                'address_list_name' => 'agy_list_netflix',
                'fqdn_domains' => ['netflix.com', 'nflxvideo.net', 'nflxext.com', 'nflximg.net'],
                'tls_host' => '*netflix.com,*nflxvideo.net,*nflxext.com,*nflximg.net',
                'tcp_ports' => '80,443',
                'udp_ports' => null,
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Vidio & Local OTT',
                'category' => 'video',
                'mark_name' => 'agy_cm_vidio',
                'address_list_name' => 'agy_list_vidio',
                'fqdn_domains' => ['vidio.com', 'rctiplus.com', 'visionplus.id', 'maxstream.tv', 'hotstar.com'],
                'tls_host' => '*vidio.com,*rctiplus.com,*visionplus.id,*maxstream.tv,*hotstar.com',
                'tcp_ports' => '80,443',
                'udp_ports' => null,
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],

            // 2. Social Media & Messaging
            [
                'name' => 'WhatsApp & Call',
                'category' => 'social_media',
                'mark_name' => 'agy_cm_whatsapp',
                'address_list_name' => 'agy_list_whatsapp',
                'fqdn_domains' => ['whatsapp.com', 'whatsapp.net'],
                'tls_host' => '*whatsapp.net,*whatsapp.com',
                'tcp_ports' => '5222,5223,5228,4244',
                'udp_ports' => '3478,5349',
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Instagram & Facebook',
                'category' => 'social_media',
                'mark_name' => 'agy_cm_meta',
                'address_list_name' => 'agy_list_meta',
                'fqdn_domains' => ['instagram.com', 'cdninstagram.com', 'facebook.com', 'fbcdn.net'],
                'tls_host' => '*instagram.com,*cdninstagram.com,*fbcdn.net,*facebook.com,*facebook.net',
                'tcp_ports' => '80,443',
                'udp_ports' => '443',
                'is_quic' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Telegram',
                'category' => 'social_media',
                'mark_name' => 'agy_cm_telegram',
                'address_list_name' => 'agy_list_telegram',
                'fqdn_domains' => ['telegram.org', 't.me', 'telegram.me'],
                'tls_host' => '*telegram.org,*t.me,*telegram.me',
                'tcp_ports' => '443,80,5222',
                'udp_ports' => null,
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Twitter / X & Threads',
                'category' => 'social_media',
                'mark_name' => 'agy_cm_twitter',
                'address_list_name' => 'agy_list_twitter',
                'fqdn_domains' => ['twitter.com', 'x.com', 'twimg.com', 'threads.net'],
                'tls_host' => '*twimg.com,*twitter.com,*x.com,*threads.net',
                'tcp_ports' => '80,443',
                'udp_ports' => null,
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],

            // 3. Online Gaming
            [
                'name' => 'Mobile Legends: Bang Bang',
                'category' => 'gaming',
                'mark_name' => 'agy_cm_mlbb',
                'address_list_name' => 'agy_list_mlbb',
                'fqdn_domains' => ['mobilelegends.com', 'moonton.com'],
                'tls_host' => '*mobilelegends.com,*moonton.com,*youngjoygame.com',
                'tcp_ports' => '5000-5200,5500-5700,30000-30200',
                'udp_ports' => '5000-5200,5500-5700,30000-30200,10003,30000-30300',
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Free Fire & Garena',
                'category' => 'gaming',
                'mark_name' => 'agy_cm_freefire',
                'address_list_name' => 'agy_list_freefire',
                'fqdn_domains' => ['freefiremobile.com', 'garena.com'],
                'tls_host' => '*freefiremobile.com,*garena.com,*garenanow.com',
                'tcp_ports' => '10000-10010,7006',
                'udp_ports' => '10000-10010,7006,39003,39698,39798',
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'PUBG Mobile',
                'category' => 'gaming',
                'mark_name' => 'agy_cm_pubg',
                'address_list_name' => 'agy_list_pubg',
                'fqdn_domains' => ['pubgmobile.com', 'proximabeta.com'],
                'tls_host' => '*pubgmobile.com,*proximabeta.com',
                'tcp_ports' => '17500,18081',
                'udp_ports' => '17500,18081,10012,20000-20002',
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Roblox & Steam',
                'category' => 'gaming',
                'mark_name' => 'agy_cm_roblox_steam',
                'address_list_name' => 'agy_list_roblox_steam',
                'fqdn_domains' => ['roblox.com', 'steampowered.com', 'steamcommunity.com'],
                'tls_host' => '*roblox.com,*rbxcdn.com,*steampowered.com,*steamcommunity.com',
                'tcp_ports' => '27015-27030',
                'udp_ports' => '27015-27030,27036-27037',
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Riot Games & Valorant',
                'category' => 'gaming',
                'mark_name' => 'agy_cm_riot',
                'address_list_name' => 'agy_list_riot',
                'fqdn_domains' => ['riotgames.com', 'playvalorant.com'],
                'tls_host' => '*riotgames.com,*playvalorant.com,*pvp.net',
                'tcp_ports' => '2099,5222,5223,8393-8400',
                'udp_ports' => '7000-8000,5000-5500',
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],

            // 4. Cloud & Work / Meet
            [
                'name' => 'Zoom & Google Meet',
                'category' => 'cloud_work',
                'mark_name' => 'agy_cm_zoom_meet',
                'address_list_name' => 'agy_list_zoom_meet',
                'fqdn_domains' => ['zoom.us', 'zoom.com', 'meet.google.com', 'teams.microsoft.com'],
                'tls_host' => '*zoom.us,*zoom.com,*meet.google.com,*teams.microsoft.com',
                'tcp_ports' => '8801,8802',
                'udp_ports' => '8801,8802,3478-3481',
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Google Drive & Cloud Storage',
                'category' => 'cloud_work',
                'mark_name' => 'agy_cm_cloud_drive',
                'address_list_name' => 'agy_list_drive',
                'fqdn_domains' => ['drive.google.com', 'onedrive.live.com', 'dropbox.com'],
                'tls_host' => '*drive.google.com,*onedrive.live.com,*dropbox.com,*icloud.com',
                'tcp_ports' => '80,443',
                'udp_ports' => null,
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],

            // 5. Web Browsing & E-Commerce
            [
                'name' => 'Shopee & Tokopedia',
                'category' => 'browsing',
                'mark_name' => 'agy_cm_ecommerce',
                'address_list_name' => 'agy_list_ecommerce',
                'fqdn_domains' => ['shopee.co.id', 'tokopedia.com', 'lazada.co.id', 'bukalapak.com'],
                'tls_host' => '*shopee.co.id,*shopeemobile.com,*tokopedia.com,*tokopedia.net,*lazada.co.id,*bukalapak.com',
                'tcp_ports' => '80,443',
                'udp_ports' => null,
                'is_quic' => false,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    /**
     * Seed or reset all default system signatures.
     */
    public static function seedDefaults(): void
    {
        $defaults = self::getDefaultDefinitions();

        foreach ($defaults as $def) {
            self::updateOrCreate(
                ['mark_name' => $def['mark_name']],
                $def
            );
        }
    }
}
