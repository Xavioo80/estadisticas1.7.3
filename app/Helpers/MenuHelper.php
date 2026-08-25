<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMenuGroups()
    {
        return [
            [
                'title' => 'MENÚ PRINCIPAL',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'subItems' => [
                            ['name' => 'General', 'path' => '/dashboard'],
                            ['name' => 'Dash. Vigilancia', 'path' => '/informes/dashboard-epi'],
                            ['name' => 'Dash. Diagnósticos', 'path' => '/informes/dashboard2'],
                        ],
                    ],
                    [
                        'icon' => 'notification-svs',
                        'name' => 'Notificación SVS',
                        'path' => '/notificacion-svs',
                    ],
                    [
                        'icon' => 'calendar',
                        'name' => 'Calendario Epi',
                        'path' => '/informes/calendario-epi',
                    ],
                    [
                        'icon' => 'ingreso-datos',
                        'name' => 'Ingreso de Datos',
                        'path' => '/ingresos',
                    ],
                ]
            ],
            [
                'title' => 'REPORTES Y SALIDA',
                'items' => [
                    [
                        'icon' => 'registros-at1',
                        'name' => 'Registros AT1',
                        'path' => '/registros',
                    ],
                    [
                        'icon' => 'informes-at1',
                        'name' => 'Informes AT1',
                        'path' => '/informes',
                    ],
                    [
                        'icon' => 'documentacion',
                        'name' => 'Documentación',
                        'path' => '/documentacion',
                    ],
                    [
                        'icon' => 'informes',
                        'name' => 'Informes',
                        'subItems' => [
                            ['icon' => 'atenciones', 'name' => 'Atenciones', 'path' => '/informes/atenciones'],
                            ['icon' => 'tb9', 'name' => 'TB9', 'path' => '/informes/tb9'],
                            ['icon' => 'implantes', 'name' => 'Implantes', 'path' => '/informes/implantes'],
                            ['icon' => 'at2-r-n', 'name' => 'AT2-r N', 'path' => '/informes/at2r-n'],
                            ['icon' => 'morbilidad', 'name' => 'Morbilidad', 'path' => '/informes/morbilidad'],
                            ['icon' => 'its', 'name' => 'ITS', 'path' => '/informes/its'],
                            ['icon' => 'alerta-semanal', 'name' => 'Alerta Semanal', 'path' => '/informes/alerta-semanal'],
                            ['icon' => 'trans-2', 'name' => 'TRANS-2', 'path' => '/informes/trans2'],
                            ['icon' => 'sm1-07', 'name' => 'SM1-07', 'path' => '/informes/sm107'],
                            ['icon' => 'sm2', 'name' => 'SM2', 'path' => '/informes/sm2'],
                            ['icon' => 'sm3-07', 'name' => 'SM3-07', 'path' => '/informes/sm307'],
                            ['icon' => 'hora-medico', 'name' => 'Hora Médico', 'path' => '/informes/hora-medico'],
                        ],
                    ],
                ]
            ],
            [
                'title' => 'GESTIÓN OTRAS BASES',
                'items' => [
                    [
                        'icon' => 'pacientes-bd',
                        'name' => 'Pacientes BD',
                        'path' => '/pacientes',
                    ],
                    [
                        'icon' => 'adolescentes',
                        'name' => 'Adolescentes',
                        'subItems' => [
                            ['name' => 'Base Adolescentes', 'path' => '/adolescentes'],
                            ['name' => 'Seguimientos', 'path' => '/adolescentes/seguimientos'],
                        ],
                    ],
                    [
                        'icon' => 'adulto-mayor',
                        'name' => 'Adulto Mayor',
                        'path' => '/adulto-mayor',
                    ],
                ]
            ],
            [
                'title' => 'ADMINISTRACIÓN',
                'items' => [
                    [
                        'icon' => 'medicos',
                        'name' => 'Médicos',
                        'path' => '/medicos',
                    ],
                    [
                        'icon' => 'diagnosticos',
                        'name' => 'Diagnósticos',
                        'path' => '/diagnosticos',
                    ],
                    [
                        'icon' => 'colonias',
                        'name' => 'Colonias',
                        'path' => '/colonias',
                    ],
                    [
                        'icon' => 'referencias',
                        'name' => 'Referencias',
                        'path' => '/referencias',
                    ],
                ]
            ],
            [
                'title' => 'MÓDULO ADMIN',
                'items' => [
                    [
                        'icon' => 'customization',
                        'name' => 'Personalización',
                        'path' => '/customization',
                    ],
                    [
                        'icon' => 'ui-elements',
                        'name' => 'Componentes UI',
                        'path' => '/components',
                    ],
                ]
            ],
        ];
    }

    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function getIconSvg($iconName)
    {
        $icons = [
            // Home / Dashboard
            'dashboard' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',

            // Notificación SVS
            'notification-svs' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',

            // Pacientes BD
            'pacientes-bd' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>',

            // Dash Diagnósticos & Informes AT1
            'dash-diagnosticos' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',

            // Vigilancia Epid.
            'vigilancia-epid' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>',

            // Calendario Epi
            'calendar' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',

            // Ingreso de Datos
            'ingreso-datos' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',

            // Registros AT1
            'registros-at1' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>',

            // Informes AT1 / Informes
            'informes-at1' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',

            'informes' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',

            // Documentación
            'documentacion' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>',

            // Adolescentes
            'adolescentes' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',

            // Adulto Mayor
            'adulto-mayor' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>',

            // ADMINISTRACIÓN Section Icons:
            // Médicos
            'medicos' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',

            // Diagnósticos
            'diagnosticos' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M19 11v6"></path><path d="M16 14h6"></path></svg>',

            // Colonias (Ubicación / Pin)
            'colonias' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',

            // Referencias (Flechas Intercambio)
            'referencias' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>',

            // Submenu Informes Specific Icons:
            'atenciones' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><line x1="12" y1="11" x2="12" y2="15"></line><line x1="10" y1="13" x2="14" y2="13"></line></svg>',
            'tb9' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16M8 8c-2 0-4 1.5-4 4.5S6 19 9 19c1.5 0 3-.5 3-2M16 8c2 0 4 1.5 4 4.5S18 19 15 19c-1.5 0-3-.5-3-2"/></svg>',
            'implantes' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 2 4 4M17 7l3-3M19 9l-8.5 8.5c-.8.8-2 .8-2.8 0L4.3 14.1c-.8-.8-.8-2 0-2.8L12.8 2.8c.8-.8 2-.8 2.8 0zM5 19l-3 3M9 15l-4 4"/></svg>',
            'at2-r-n' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4M12 16h4M8 11h.01M8 16h.01"/></svg>',
            'morbilidad' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1"/></svg>',
            'its' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="9" x2="12" y2="15"/><line x1="9" y1="12" x2="15" y2="12"/></svg>',
            'alerta-semanal' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            'trans-2' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 14h2l1-3 2 6 1-3h2"/></svg>',
            'sm1-07' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15A2.5 2.5 0 0 1 9.5 22h-1A3.5 3.5 0 0 1 5 18.5V17a3.5 3.5 0 0 1 .5-1.9 3.5 3.5 0 0 1-1-6.6A3.5 3.5 0 0 1 8.5 4h1zM14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 2.5 2.5h1a3.5 3.5 0 0 0 3.5-3.5V17a3.5 3.5 0 0 0-.5-1.9 3.5 3.5 0 0 0 1-6.6A3.5 3.5 0 0 0 15.5 4h-1z"/></svg>',
            'sm2' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
            'sm3-07' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>',
            'hora-medico' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',

            // Personalización
            'customization' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',

            // Componentes UI
            'ui-elements' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>',
        ];

        return $icons[$iconName] ?? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
    }
}
