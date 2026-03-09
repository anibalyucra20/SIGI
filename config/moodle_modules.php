<?php

/**
 * Archivo de configuración de módulos para integración SIGI - Moodle
 * Define campos necesarios para crear actividades y recursos vía core_course_create_module.
 */

return [
    'common' => [
        ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'required' => true],
        ['name' => 'intro', 'label' => 'Descripción', 'type' => 'editor', 'required' => false],
        ['name' => 'introformat', 'label' => 'Formato descripción', 'type' => 'hidden', 'default' => 1],
        ['name' => 'visible', 'label' => 'Visible', 'type' => 'bool', 'required' => false, 'default' => true],
        ['name' => 'showdescription', 'label' => 'Mostrar descripción en curso', 'type' => 'bool', 'required' => false, 'default' => false],
        ['name' => 'groupmode', 'label' => 'Modo de grupo', 'type' => 'int', 'required' => false, 'default' => 0],
        // NUEVO: Rastreo de finalización (0 = No, 1 = Manual, 2 = Automático)
        //['name' => 'completion', 'label' => 'Rastreo de finalización', 'type' => 'select', 'default' => 1, 'options' => [0 => 'No rastrear', 1 => 'Los estudiantes pueden marcarla', 2 => 'Mostrar como completada cuando se cumplan condiciones']],
    ],

    'types' => [
        // ======== CONTENEDORES ========
        'subsection' => [
            'category' => 'container',
            'label' => 'Subsección',
            'graded' => false,
            'fields' => [],
        ],

        // ======== RECURSOS ========
        'label' => [
            'category' => 'resource',
            'label' => 'Área de texto y medios',
            'graded' => false,
            'fields' => [],
        ],
        'page' => [
            'category' => 'resource',
            'label' => 'Página',
            'graded' => false,
            'fields' => [
                ['name' => 'content', 'label' => 'Contenido de la página', 'type' => 'editor', 'required' => true],
                ['name' => 'contentformat', 'label' => 'Formato contenido', 'type' => 'hidden', 'default' => 1],
            ],
        ],
        'url' => [
            'category' => 'resource',
            'label' => 'URL',
            'graded' => false,
            'fields' => [
                ['name' => 'externalurl', 'label' => 'Enlace (URL)', 'type' => 'url', 'required' => true],
                [
                    'name' => 'display',
                    'label' => 'Apariencia',
                    'type' => 'select',
                    'default' => 0,
                    'options' => [
                        0 => 'Automático',
                        1 => 'Incrustar',
                        3 => 'Abrir',
                        5 => 'En ventana emergente'
                    ]
                ],
            ],
        ],
        'resource' => [
            'category' => 'resource',
            'label' => 'Archivo',
            'graded' => false,
            'fields' => [
                ['name' => 'files_filemanager', 'label' => 'Archivo', 'type' => 'file', 'required' => true],
                [
                    'name' => 'display',
                    'label' => 'Mostrar',
                    'type' => 'select',
                    'default' => 0,
                    'options' => [
                        0 => 'Automático',
                        1 => 'Incrustar',
                        2 => 'Forzar descarga',
                        3 => 'Abrir',
                        4 => 'En ventana emergente'
                    ]
                ],
            ],
        ],
        'folder' => [
            'category' => 'resource',
            'label' => 'Carpeta',
            'graded' => false,
            'fields' => [
                ['name' => 'files_filemanager', 'label' => 'Archivos', 'type' => 'file', 'required' => true],
                [
                    'name' => 'display',
                    'label' => 'Mostrar contenido',
                    'type' => 'select',
                    'default' => 0,
                    'options' => [
                        0 => 'En una página separada',
                        1 => 'En línea en la página del curso'
                    ]
                ],
            ],
        ],
        'book' => [
            'category' => 'resource',
            'label' => 'Libro',
            'graded' => false,
            'fields' => [
                ['name' => 'numbering', 'label' => 'Numeración capítulos', 'type' => 'int', 'default' => 1],
            ],
        ],
        'imscp' => [
            'category' => 'resource',
            'label' => 'Paquete IMS',
            'graded' => false,
            'needs_file' => true,
            'fields' => [
                ['name' => 'package_filemanager', 'label' => 'Paquete IMS', 'type' => 'file', 'required' => true],
            ],
        ],

        // ======== ACTIVIDADES ========
        'assign' => [
            'category' => 'activity',
            'label' => 'Tarea',
            'graded' => true,
            'fields' => [
                ['name' => 'grade', 'label' => 'Puntaje máximo', 'type' => 'int', 'default' => 20],
                ['name' => 'allowsubmissionsfromdate', 'label' => 'Permitir entregas desde', 'type' => 'datetime'],
                ['name' => 'duedate', 'label' => 'Fecha límite de entrega', 'type' => 'datetime'],
                ['name' => 'cutoffdate', 'label' => 'Fecha de corte (Cierre estricto)', 'type' => 'datetime'],
                ['name' => 'assignsubmission_file_enabled', 'label' => 'Permitir subir archivos', 'type' => 'bool', 'default' => 1],
                ['name' => 'assignsubmission_onlinetext_enabled', 'label' => 'Permitir texto en línea', 'type' => 'bool', 'default' => 0],
            ],
        ],
        'quiz' => [
            'category' => 'activity',
            'label' => 'Cuestionario',
            'graded' => true,
            'fields' => [
                ['name' => 'grade', 'label' => 'Puntaje máximo', 'type' => 'int', 'default' => 20],
                ['name' => 'timeopen', 'label' => 'Abrir cuestionario', 'type' => 'datetime'],
                ['name' => 'timeclose', 'label' => 'Cerrar cuestionario', 'type' => 'datetime'],
                ['name' => 'timelimit', 'label' => 'Límite de tiempo (Minutos)', 'type' => 'int', 'default' => 0],
                [
                    'name' => 'overduehandling',
                    'label' => 'Cuando el tiempo ha terminado',
                    'type' => 'select',
                    'default' => 'autosubmit',
                    'options' => [
                        'autosubmit' => 'El envío se realiza automáticamente',
                        'graceperiod' => 'Hay un período de gracia para enviar',
                        'autoabandon' => 'El envío debe hacerse antes de terminar'
                    ]
                ],
            ],
        ],
        'forum' => [
            'category' => 'activity',
            'label' => 'Foro',
            'graded' => true,
            'fields' => [
                [
                    'name' => 'type',
                    'label' => 'Tipo de foro',
                    'type' => 'select',
                    'default' => 'general',
                    'options' => ['general' => 'Uso general', 'eachuser' => 'Cada uno un tema', 'blog' => 'Tipo blog', 'qanda' => 'Preguntas/Respuestas']
                ],
                [
                    'name' => 'forcesubscribe',
                    'label' => 'Suscripción forzada',
                    'type' => 'select',
                    'default' => 0,
                    'options' => [0 => 'Opcional', 1 => 'Forzada', 2 => 'Automática', 3 => 'Desactivada']
                ],
                // CORRECCIÓN: Agregar tipo de consolidación y puntaje
                [
                    'name' => 'assessed',
                    'label' => 'Tipo de consolidación',
                    'type' => 'select',
                    'default' => 1, // 1 = Promedio de calificaciones
                    'options' => [0 => 'No hay calificaciones', 1 => 'Promedio', 2 => 'Número de calificaciones', 3 => 'Calificación máxima']
                ],
                ['name' => 'scale', 'label' => 'Puntaje máximo', 'type' => 'int', 'default' => 20],
                ['name' => 'duedate', 'label' => 'Fecha límite (Para entrega)', 'type' => 'datetime'],
                ['name' => 'cutoffdate', 'label' => 'Fecha de corte (Para participar)', 'type' => 'datetime'],
            ],
        ],
        'choice' => [
            'category' => 'activity',
            'label' => 'Consulta',
            'graded' => false,
            'fields' => [
                ['name' => 'timeopen', 'label' => 'Abrir desde', 'type' => 'datetime'],
                ['name' => 'timeclose', 'label' => 'Cerrar en', 'type' => 'datetime'],
                ['name' => 'allowupdate', 'label' => 'Permitir actualizar', 'type' => 'bool', 'default' => false],
                [
                    'name' => 'display',
                    'label' => 'Visualización de opciones',
                    'type' => 'select',
                    'default' => 0,
                    'options' => [
                        0 => 'Horizontal',
                        1 => 'Vertical'
                    ]
                ],
            ],
        ],
        'glossary' => [
            'category' => 'activity',
            'label' => 'Glosario',
            'graded' => true,
            'fields' => [
                ['name' => 'mainglossary', 'label' => 'Tipo de glosario', 'type' => 'select', 'default' => 0, 'options' => [0 => 'Secundario', 1 => 'Principal']],
                ['name' => 'defaultapproval', 'label' => 'Aprobación automática', 'type' => 'bool', 'default' => true],
                // CORRECCIÓN: Agregar consolidación y nota
                ['name' => 'assessed', 'label' => 'Tipo de consolidación', 'type' => 'select', 'default' => 1, 'options' => [0 => 'Sin calificaciones', 1 => 'Promedio']],
                ['name' => 'scale', 'label' => 'Puntaje máximo', 'type' => 'int', 'default' => 20],
            ],
        ],
        'scorm' => [
            'category' => 'activity',
            'label' => 'Paquete SCORM',
            'graded' => true,
            'needs_file' => true,
            'fields' => [
                ['name' => 'packagefile', 'label' => 'Archivo SCORM', 'type' => 'file', 'required' => true],
                ['name' => 'timeopen', 'label' => 'Disponible desde', 'type' => 'datetime'],
                ['name' => 'timeclose', 'label' => 'Disponible hasta', 'type' => 'datetime'],
                [
                    'name' => 'grademethod',
                    'label' => 'Método calificación',
                    'type' => 'select',
                    'default' => 1,
                    'options' => [
                        '1' => 'Objetos de aprendizaje (SCO)',
                        '2' => 'Calificación más alta',
                        '3' => 'Calificación promedio',
                        '4' => 'Calificación sumada'
                    ]
                ],
                ['name' => 'maxgrade', 'label' => 'Nota máxima', 'type' => 'int', 'default' => 20],
            ],
        ],
        'lesson' => [
            'category' => 'activity',
            'label' => 'Lección',
            'graded' => true,
            'fields' => [
                ['name' => 'grade', 'label' => 'Nota máxima', 'type' => 'int', 'default' => 20],
                ['name' => 'available', 'label' => 'Disponible desde', 'type' => 'datetime'],
                ['name' => 'deadline', 'label' => 'Fecha límite', 'type' => 'datetime'],
                ['name' => 'timelimit', 'label' => 'Tiempo límite (minutos)', 'type' => 'int', 'default' => 0],
            ],
        ],
        'data' => [
            'category' => 'activity',
            'label' => 'Base de datos',
            'graded' => true,
            'fields' => [
                ['name' => 'timeavailablefrom', 'label' => 'Disponible desde', 'type' => 'datetime'],
                ['name' => 'timeavailableto', 'label' => 'Disponible hasta', 'type' => 'datetime'],
                ['name' => 'timeviewfrom', 'label' => 'Solo lectura desde', 'type' => 'datetime'],
                ['name' => 'timeviewto', 'label' => 'Solo lectura hasta', 'type' => 'datetime'],
                ['name' => 'approval', 'label' => 'Requerir aprobación', 'type' => 'bool', 'default' => false],
                // CORRECCIÓN: Consolidación y nota
                ['name' => 'assessed', 'label' => 'Tipo de consolidación', 'type' => 'select', 'default' => 1, 'options' => [0 => 'Sin calificaciones', 1 => 'Promedio']],
                ['name' => 'scale', 'label' => 'Puntaje máximo', 'type' => 'int', 'default' => 20],
            ],
        ],
        'workshop' => [
            'category' => 'activity',
            'label' => 'Taller',
            'graded' => true,
            'fields' => [
                ['name' => 'submissionstart', 'label' => 'Inicio de envíos', 'type' => 'datetime'],
                ['name' => 'submissionend', 'label' => 'Fin de envíos', 'type' => 'datetime'],
                ['name' => 'assessmentstart', 'label' => 'Inicio de evaluaciones', 'type' => 'datetime'],
                ['name' => 'assessmentend', 'label' => 'Fin de evaluaciones', 'type' => 'datetime'],
                ['name' => 'grade', 'label' => 'Nota por el envío', 'type' => 'int', 'default' => 10], // Taller suele dividir notas
                ['name' => 'gradinggrade', 'label' => 'Nota por la evaluación (coevaluación)', 'type' => 'int', 'default' => 10],
            ],
        ],
        'wiki' => [
            'category' => 'activity',
            'label' => 'Wiki',
            'graded' => false,
            'fields' => [
                ['name' => 'wikimode', 'label' => 'Modo Wiki', 'type' => 'select', 'default' => 'collaborative', 'options' => ['collaborative' => 'Colaborativa', 'individual' => 'Individual']],
                ['name' => 'firstpagetitle', 'label' => 'Título primera página', 'type' => 'text', 'required' => true],
            ],
        ],
        'feedback' => [
            'category' => 'activity',
            'label' => 'Encuesta',
            'graded' => false,
            'fields' => [
                ['name' => 'timeopen', 'label' => 'Abrir desde', 'type' => 'datetime'],
                ['name' => 'timeclose', 'label' => 'Cerrar en', 'type' => 'datetime'],
                ['name' => 'anonymous', 'label' => 'Tipo de respuesta', 'type' => 'select', 'default' => 1, 'options' => [1 => 'Anónimo', 2 => 'Nombres de usuarios']],
            ],
        ],
        'h5pactivity' => [
            'category' => 'activity',
            'label' => 'H5P',
            'graded' => true,
            'needs_file' => true,
            'fields' => [
                ['name' => 'packagepath', 'label' => 'Archivo H5P', 'type' => 'file', 'required' => true],
                ['name' => 'grade', 'label' => 'Nota máxima', 'type' => 'int', 'default' => 20],
            ],
        ],
    ],
];
