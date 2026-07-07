<x-app-layout>
    <div style="max-width: 600px; margin: 40px auto; padding: 0 20px;">
        <!-- CONTENEDOR PRINCIPAL INTERACTIVO CON ALPINE.JS -->
        <div x-data="{ 
            rating: 0, 
            hoverRating: 0, 
            comment: '', 
            feedbacks: JSON.parse(localStorage.getItem('ticketus_feedbacks') || '[]'),
            enviarFeedback() {
                if(this.rating === 0) { alert('Por favor, selecciona una calificación de estrellas.'); return; }
                if(this.comment.trim() === '') { alert('Por favor, escribe un comentario.'); return; }
                
                const nuevoFeedback = {
                    estrellas: this.rating,
                    mensaje: this.comment,
                    fecha: new Date().toLocaleDateString()
                };
                
                this.feedbacks.unshift(nuevoFeedback);
                localStorage.setItem('ticketus_feedbacks', JSON.stringify(this.feedbacks));
                
                // Limpiar formulario
                this.rating = 0;
                this.comment = '';
                alert('¡Gracias por tu feedback!');
            }
        }" 
        style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
            
            <h2 style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 8px; text-align: center;">Califica tu Experiencia</h2>
            <p style="color: #94a3b8; font-size: 14px; text-align: center; margin-bottom: 24px;">Tu opinión nos ayuda a mejorar el Sistema de Gestión de Tickets.</p>

            <!-- SISTEMA DE ESTRELLAS -->
            <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 24px;">
                <template x-for="i in 5">
                    <button type="button" 
                            @click="rating = i" 
                            @mouseenter="hoverRating = i" 
                            @mouseleave="hoverRating = 0"
                            style="background: transparent; border: none; cursor: pointer; font-size: 36px; padding: 0; outline: none; transition: transform 0.1s;">
                        <span x-text="(hoverRating || rating) >= i ? '★' : '☆'"
                              :style="{ color: (hoverRating || rating) >= i ? '#eab308' : '#64748b' }"></span>
                    </button>
                </template>
            </div>

            <!-- CUADRO DE MENSAJE -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #cbd5e1; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Tu Comentario:</label>
                <textarea x-model="comment" 
                          rows="4" 
                          placeholder="Escribe aquí tus sugerencias o comentarios sobre TicketUS..."
                          style="width: 100%; background-color: #0f172a; color: #f1f5f9; border: 1px solid #475569; border-radius: 6px; padding: 12px; font-size: 14px; resize: vertical; outline: none;"></textarea>
            </div>

            <!-- BOTÓN ENVIAR -->
            <button type="button" 
                    @click="enviarFeedback()"
                    style="width: 100%; background-color: #2563eb; color: #ffffff; font-size: 14px; font-weight: 600; padding: 12px; border: none; border-radius: 6px; cursor: pointer; transition: background-color 0.2s;">
                Enviar Comentarios
            </button>

            <!-- HISTORIAL DE COMENTARIOS LOCALES -->
            <div style="margin-top: 40px; border-top: 1px solid #334155; padding-top: 24px;">
                <h3 style="color: #ffffff; font-size: 16px; font-weight: 600; margin-bottom: 16px;">Comentarios Recientes (Guardados en este navegador)</h3>
                
                <template x-if="feedbacks.length === 0">
                    <p style="color: #64748b; font-size: 14px; font-style: italic; text-align: center;">No hay feedbacks registrados aún.</p>
                </template>

                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 250px; overflow-y: auto; padding-right: 4px;">
                    <template x-for="fb in feedbacks">
                        <div style="background-color: #0f172a; padding: 12px 16px; border-radius: 6px; border: 1px solid #334155;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <div style="color: #eab308; font-size: 14px;">
                                    <template x-for="star in fb.estrellas"><span>★</span></template>
                                </div>
                                <span x-text="fb.fecha" style="color: #64748b; font-size: 11px;"></span>
                            </div>
                            <p x-text="fb.mensaje" style="color: #e2e8f0; font-size: 13px; margin: 0; word-break: break-word;"></p>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>