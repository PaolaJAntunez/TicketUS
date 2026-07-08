<x-app-layout>
    <div x-data="{ 
        idioma: localStorage.getItem('ticketus_lang') || 'es',
        faqAbierta: null,
        buscar: '',
        
        textosFaqs: {
            es: {
                titulo: 'Centro de Ayuda y FAQs',
                subtitulo: 'Busca respuestas rápidas antes de abrir un ticket de soporte.',
                placeholderBuscar: '¿Con qué necesitas ayuda hoy?...',
                tituloSeccion: 'Preguntas Frecuentes',
                
                // Bloque lateral de Soporte Directo
                soporteTitulo: '¿No encontraste solución?',
                soporteDesc: 'Nuestro equipo de TI está listo para ayudarte en canales directos.',
                soporteHorario: 'Horario: Lun - Vie (8:00 AM - 6:00 PM)',
                soporteExt: 'Extensión TI: 4000',
                soporteEmail: 'Soporte: ti@ticketus.com',
                
                // Preguntas y Respuestas
                q1: '¿Cuánto tiempo tarda en resolverse un ticket?',
                a1: 'Los tickets de prioridad alta se atienden en menos de 2 horas. Los de prioridad media o baja pueden tardar hasta 24 horas hábiles.',
                q2: '¿Cómo restablezco mi contraseña de la VPN?',
                a2: 'Puedes hacerlo de forma autónoma desde el portal de autoservicio de TI o solicitando un token temporal en la pestaña de configuración.',
                q3: '¿Quién puede aprobar mis solicitudes de software?',
                a3: 'Cualquier usuario con el rol de Aprobador o Administrador asignado a tu departamento.',
                q4: '¿Cómo reportar un equipo dañado físicamente?',
                a4: 'Crea un ticket bajo la categoría Hardware e incluye fotos del daño. El departamento de inventarios gestionará tu cambio.'
            },
            en: {
                titulo: 'Help Center & FAQs',
                subtitulo: 'Find quick answers before opening a support ticket.',
                placeholderBuscar: 'What do you need help with today?...',
                tituloSeccion: 'Frequently Asked Questions',
                
                soporteTitulo: 'Didn\'t find a solution?',
                soporteDesc: 'Our IT team is ready to assist you through direct channels.',
                soporteHorario: 'Hours: Mon - Fri (8:00 AM - 6:00 PM)',
                soporteExt: 'IT Extension: 4000',
                soporteEmail: 'Support: it@ticketus.com',
                
                q1: 'How long does it take to resolve a ticket?',
                a1: 'High priority tickets are addressed in less than 2 hours. Medium or low priority tickets may take up to 24 business hours.',
                q2: 'How do I reset my VPN password?',
                a2: 'You can do it autonomously from the IT self-service portal or by requesting a temporary token in the settings tab.',
                q3: 'Who can approve my software requests?',
                a3: 'Any user with the Approver or Administrator role assigned to your department.',
                q4: 'How do I report physically damaged equipment?',
                a4: 'Create a ticket under the Hardware category and upload photos of the damage. The inventory department will handle your replacement.'
            }
        },

        cumpleFiltro(pregunta, respuesta) {
            if (!this.buscar) return true;
            let term = this.buscar.toLowerCase();
            return pregunta.toLowerCase().includes(term) || respuesta.toLowerCase().includes(term);
        }
    }" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">

        <!-- ENCABEZADO CON BUSCADOR -->
        <div style="text-align: center; margin-bottom: 48px;">
            <h1 x-text="textosFaqs[idioma].titulo" style="font-size: 32px; font-weight: 700; color: #ffffff; margin-bottom: 8px;"></h1>
            <p x-text="textosFaqs[idioma].subtitulo" style="font-size: 16px; color: #94a3b8; margin-bottom: 24px;"></p>
            
            <div style="max-width: 600px; margin: 0 auto; position: relative;">
                <input type="text" 
                       x-model="buscar" 
                       :placeholder="textosFaqs[idioma].placeholderBuscar"
                       style="width: 100%; padding: 14px 20px 14px 45px; background-color: #1e293b; border: 1px solid #334155; border-radius: 30px; color: #ffffff; font-size: 15px; outline: none; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                <span style="position: absolute; left: 18px; top: 14px; color: #64748b; font-size: 18px;">🔍</span>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL: DOS COLUMNAS -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start;">
            
            <!-- ACORDEÓN DE PREGUNTAS -->
            <div>
                <h2 x-text="textosFaqs[idioma].tituloSeccion" style="color: #ffffff; font-size: 20px; font-weight: 600; margin-bottom: 16px;"></h2>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    
                    <!-- FAQ 1 -->
                    <div x-show="cumpleFiltro(textosFaqs[idioma].q1, textosFaqs[idioma].a1)" style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; overflow: hidden;">
                        <button @click="faqAbierta = (faqAbierta === 1 ? null : 1)" style="width: 100%; padding: 16px; background: transparent; border: none; text-align: left; color: #f1f5f9; font-weight: 500; font-size: 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span x-text="textosFaqs[idioma].q1"></span>
                            <span x-text="faqAbierta === 1 ? '▲' : '▼'" style="font-size: 10px; color: #64748b;"></span>
                        </button>
                        <div x-show="faqAbierta === 1" style="padding: 0 16px 16px 16px; color: #94a3b8; font-size: 14px; line-height: 1.6;" x-cloak>
                            <p x-text="textosFaqs[idioma].a1" style="margin: 0;"></p>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div x-show="cumpleFiltro(textosFaqs[idioma].q2, textosFaqs[idioma].a2)" style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; overflow: hidden;">
                        <button @click="faqAbierta = (faqAbierta === 2 ? null : 2)" style="width: 100%; padding: 16px; background: transparent; border: none; text-align: left; color: #f1f5f9; font-weight: 500; font-size: 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span x-text="textosFaqs[idioma].q2"></span>
                            <span x-text="faqAbierta === 2 ? '▲' : '▼'" style="font-size: 10px; color: #64748b;"></span>
                        </button>
                        <div x-show="faqAbierta === 2" style="padding: 0 16px 16px 16px; color: #94a3b8; font-size: 14px; line-height: 1.6;" x-cloak>
                            <p x-text="textosFaqs[idioma].a2" style="margin: 0;"></p>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div x-show="cumpleFiltro(textosFaqs[idioma].q3, textosFaqs[idioma].a3)" style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; overflow: hidden;">
                        <button @click="faqAbierta = (faqAbierta === 3 ? null : 3)" style="width: 100%; padding: 16px; background: transparent; border: none; text-align: left; color: #f1f5f9; font-weight: 500; font-size: 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span x-text="textosFaqs[idioma].q3"></span>
                            <span x-text="faqAbierta === 3 ? '▲' : '▼'" style="font-size: 10px; color: #64748b;"></span>
                        </button>
                        <div x-show="faqAbierta === 3" style="padding: 0 16px 16px 16px; color: #94a3b8; font-size: 14px; line-height: 1.6;" x-cloak>
                            <p x-text="textosFaqs[idioma].a3" style="margin: 0;"></p>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div x-show="cumpleFiltro(textosFaqs[idioma].q4, textosFaqs[idioma].a4)" style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; overflow: hidden;">
                        <button @click="faqAbierta = (faqAbierta === 4 ? null : 4)" style="width: 100%; padding: 16px; background: transparent; border: none; text-align: left; color: #f1f5f9; font-weight: 500; font-size: 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span x-text="textosFaqs[idioma].q4"></span>
                            <span x-text="faqAbierta === 4 ? '▲' : '▼'" style="font-size: 10px; color: #64748b;"></span>
                        </button>
                        <div x-show="faqAbierta === 4" style="padding: 0 16px 16px 16px; color: #94a3b8; font-size: 14px; line-height: 1.6;" x-cloak>
                            <p x-text="textosFaqs[idioma].a4" style="margin: 0;"></p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- BLOQUE LATERAL: CANALES DE ATENCIÓN DIRECTA -->
            <div style="background-color: rgba(37, 99, 235, 0.08); border: 1px solid #2563eb; padding: 24px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3 x-text="textosFaqs[idioma].soporteTitulo" style="color: #ffffff; font-size: 18px; font-weight: 600; margin: 0 0 10px 0;"></h3>
                <p x-text="textosFaqs[idioma].soporteDesc" style="color: #94a3b8; font-size: 14px; margin: 0 0 18px 0; line-height: 1.5;"></p>
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: #cbd5e1;">
                    <div>🕒 <span x-text="textosFaqs[idioma].soporteHorario"></span></div>
                    <div>📞 <span x-text="textosFaqs[idioma].soporteExt"></span></div>
                    <div>✉️ <span x-text="textosFaqs[idioma].soporteEmail"></span></div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>