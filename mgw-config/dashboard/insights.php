<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>
{
    "metadata": {
        "generatedAt": "2025-11-13 21:45:00",
        "source": "site-groups",
        "notes": "Demo dataset for admin dashboard layout."
    },
    "overview": {
        "totalDelta": 4.2,
        "activeDelta": 2.3,
        "abnormalDelta": -1.8,
        "pendingDelta": 0.6
    },
    "https": {
        "valid": 18,
        "expiring": 3,
        "invalid": 2,
        "trend": [
            {"label": "11-07", "valid": 12, "expiring": 1, "invalid": 1},
            {"label": "11-08", "valid": 13, "expiring": 1, "invalid": 1},
            {"label": "11-09", "valid": 14, "expiring": 2, "invalid": 1},
            {"label": "11-10", "valid": 15, "expiring": 2, "invalid": 1},
            {"label": "11-11", "valid": 16, "expiring": 3, "invalid": 1},
            {"label": "11-12", "valid": 17, "expiring": 2, "invalid": 2},
            {"label": "11-13", "valid": 18, "expiring": 3, "invalid": 2}
        ]
    },
    "spiders": {
        "total": 18642,
        "last24h": 1328,
        "activeBots": 7,
        "topSources": [
            {"name": "Baidu Spider", "hits": 6204, "change": "+8%"},
            {"name": "Googlebot", "hits": 4820, "change": "+5%"},
            {"name": "360Spider", "hits": 3290, "change": "+2%"},
            {"name": "Sogou Spider", "hits": 2441, "change": "-3%"},
            {"name": "Bytespider", "hits": 1120, "change": "+11%"}
        ],
        "trend": [
            {"label": "11-07", "value": 2150},
            {"label": "11-08", "value": 2364},
            {"label": "11-09", "value": 3210},
            {"label": "11-10", "value": 2840},
            {"label": "11-11", "value": 3126},
            {"label": "11-12", "value": 2768},
            {"label": "11-13", "value": 2984}
        ],
        "latest": [
            {"bot": "Baidu Spider", "domain": "www.1dun.co", "path": "/", "ip": "103.21.8.12", "time": "2025-11-13 21:42:10"},
            {"bot": "Googlebot", "domain": "www.1dun.net", "path": "/news/industry", "ip": "35.191.4.202", "time": "2025-11-13 21:38:26"},
            {"bot": "360Spider", "domain": "cms.1dun.co", "path": "/api/feed", "ip": "101.226.4.92", "time": "2025-11-13 21:36:54"},
            {"bot": "Sogou Spider", "domain": "fdsafsd.com", "path": "/products/10086", "ip": "49.4.66.120", "time": "2025-11-13 21:35:08"},
            {"bot": "Bytespider", "domain": "fdsakjfkdaj.com", "path": "/blog/ai-report", "ip": "47.243.31.16", "time": "2025-11-13 21:33:17"}
        ]
    },
    "tasksSummary": {
        "date": "2025-11-13",
        "success": 42,
        "failed": 3
    },
    "latestSites": [
        {
            "name": "www.1dun.co",
            "group": "首批上线",
            "status": "online",
            "change": "从测试切换为正式上线",
            "operator": "admin",
            "updatedAt": "2025-11-13 19:45:00"
        },
        {
            "name": "cms.1dun.co",
            "group": "首批上线",
            "status": "online",
            "change": "发布 12 篇新内容",
            "operator": "admin",
            "updatedAt": "2025-11-13 19:42:00"
        },
        {
            "name": "www.1dun.net",
            "group": "测试批次",
            "status": "maintenance",
            "change": "进入维护模式",
            "operator": "tester",
            "updatedAt": "2025-11-13 17:05:00"
        },
        {
            "name": "fdsafsd.com",
            "group": "dfada",
            "status": "paused",
            "change": "审核暂缓上线",
            "operator": "admin",
            "updatedAt": "2025-11-13 16:53:40"
        },
        {
            "name": "fdsakjfkdaj.com",
            "group": "dfada",
            "status": "online",
            "change": "SSL 证书续期成功",
            "operator": "admin",
            "updatedAt": "2025-11-13 16:45:12"
        }
    ],
    "runningTasks": [
        {
            "name": "内容全量同步",
            "schedule": "0 2 * * *",
            "lastRun": "2025-11-13 02:00",
            "status": "success",
            "duration": "08m 23s"
        },
        {
            "name": "增量监控采集",
            "schedule": "*/15 * * * *",
            "lastRun": "2025-11-13 20:45",
            "status": "success",
            "duration": "03m 11s"
        },
        {
            "name": "证书有效性扫描",
            "schedule": "30 3 * * 1",
            "lastRun": "2025-11-11 03:33",
            "status": "warning",
            "duration": "12m 47s"
        },
        {
            "name": "日志归档压缩",
            "schedule": "0 4 * * *",
            "lastRun": "2025-11-13 04:00",
            "status": "success",
            "duration": "04m 02s"
        },
        {
            "name": "安全基线检查",
            "schedule": "0 */6 * * *",
            "lastRun": "2025-11-13 18:00",
            "status": "failed",
            "duration": "01m 15s"
        }
    ],
    "authorization": {
        "license": "Maigewan Enterprise",
        "status": "valid",
        "expiresAt": "2026-01-31",
        "lastChecked": "2025-11-12 09:20",
        "supportLevel": "priority",
        "authorizedTo": [
            "1dun.co",
            "1dun.net",
            "cms.1dun.co"
        ]
    },
    "recommendations": [
        {
            "title": "启用站点健康告警联动",
            "severity": "high",
            "description": "为异常站点配置短信告警，确保 15 分钟内响应。"
        },
        {
            "title": "完善蜘蛛行为白名单",
            "severity": "medium",
            "description": "将 360Spider 和 Sogou Spider 加入反爬白名单，减少误封。"
        },
        {
            "title": "每周复核 SSL 证书",
            "severity": "low",
            "description": "安排值班人员每周巡检证书到期情况，提前 10 天续期。"
        }
    ]
}
