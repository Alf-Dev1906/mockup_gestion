<template>
  <div class="home">
    <!-- Hero Carousel Section -->
    <div class="relative h-screen overflow-hidden">
      <!-- Carousel Container -->
      <div class="relative h-full">
        <!-- Slides -->
        <transition-group name="fade">
          <div
            v-for="(slide, index) in heroSlides"
            v-show="currentSlide === index"
            :key="index"
            class="absolute inset-0 w-full h-full"
          >
            <!-- Background Image with Overlay -->
            <div 
              class="absolute inset-0 bg-cover bg-center transform transition-transform duration-1000"
              :style="{ 
                backgroundImage: `url(${slide.image})`,
                transform: currentSlide === index ? 'scale(1.05)' : 'scale(1)'
              }"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 via-blue-800/80 to-transparent"></div>
            </div>

            <!-- Content -->
            <div class="relative h-full flex items-center">
              <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="max-w-3xl">
                  <div class="mb-6 animate-slideInLeft">
                    <span class="inline-block px-4 py-2 bg-yellow-500/90 text-blue-900 rounded-full text-sm font-bold uppercase tracking-wide">
                      {{ slide.badge }}
                    </span>
                  </div>
                  <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 animate-slideInLeft animation-delay-100">
                    {{ slide.title }}
                  </h1>
                  <p class="text-xl md:text-2xl text-blue-100 mb-8 animate-slideInLeft animation-delay-200">
                    {{ slide.subtitle }}
                  </p>
                  <div class="flex flex-wrap gap-4 animate-slideInLeft animation-delay-300">
                    <button 
                      @click="handleInscripcion"
                      :disabled="verificandoSolicitud"
                      class="bg-yellow-500 hover:bg-yellow-400 text-blue-900 px-8 py-4 rounded-xl font-bold text-lg transition-all transform hover:scale-105 shadow-2xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                      <span v-if="!verificandoSolicitud">Inscríbete Ahora</span>
                      <span v-else class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        Verificando...
                      </span>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                      </svg>
                    </button>
                    <router-link 
                      to="/carreras"
                      class="border-2 border-white hover:bg-white hover:text-blue-900 text-white px-8 py-4 rounded-xl font-bold text-lg transition-all transform hover:scale-105 inline-flex items-center gap-2"
                    >
                      Conoce Más
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                      </svg>
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </transition-group>
      </div>

      <!-- Carousel Controls -->
      <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20 flex gap-3">
        <button
          v-for="(slide, index) in heroSlides"
          :key="index"
          @click="currentSlide = index"
          class="group"
          :aria-label="`Ir a slide ${index + 1}`"
        >
          <div 
            class="h-2 rounded-full transition-all duration-300"
            :class="currentSlide === index ? 'w-12 bg-yellow-500' : 'w-8 bg-white/50 group-hover:bg-white/75'"
          ></div>
        </button>
      </div>

      <!-- Navigation Arrows -->
      <button 
        @click="previousSlide"
        class="absolute left-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white p-4 rounded-full transition-all group"
        aria-label="Slide anterior"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <button 
        @click="nextSlide"
        class="absolute right-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white p-4 rounded-full transition-all group"
        aria-label="Siguiente slide"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Scroll Indicator -->
      <div class="absolute bottom-8 right-8 z-20 animate-bounce">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white/75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
        </svg>
      </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-gradient-to-b from-white to-gray-50 py-20">
      <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="text-center group">
            <div class="bg-blue-100 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <div class="text-4xl md:text-5xl font-bold text-blue-900 mb-2 counter" data-target="10000">10,000+</div>
            <div class="text-gray-600 font-medium">Estudiantes</div>
          </div>
          <div class="text-center group">
            <div class="bg-green-100 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div class="text-4xl md:text-5xl font-bold text-blue-900 mb-2">{{ stats.carreras }}</div>
            <div class="text-gray-600 font-medium">Carreras</div>
          </div>
          <div class="text-center group">
            <div class="bg-purple-100 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="text-4xl md:text-5xl font-bold text-blue-900 mb-2">200+</div>
            <div class="text-gray-600 font-medium">Profesores</div>
          </div>
          <div class="text-center group">
            <div class="bg-yellow-100 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
              </svg>
            </div>
            <div class="text-4xl md:text-5xl font-bold text-blue-900 mb-2">50+</div>
            <div class="text-gray-600 font-medium">Años de Excelencia</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Carreras Section -->
    <div class="bg-gray-50 py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Nuestras Carreras</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-3xl">💻</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Ingeniería de Sistemas</h3>
            <p class="text-gray-600 mb-4">Desarrollo de software y sistemas informáticos de vanguardia.</p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Más información →</a>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-3xl">🏥</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Medicina</h3>
            <p class="text-gray-600 mb-4">Formación integral en ciencias de la salud y atención médica.</p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Más información →</a>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-3xl">⚖️</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Derecho</h3>
            <p class="text-gray-600 mb-4">Estudios jurídicos con enfoque en justicia y derechos humanos.</p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Más información →</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Noticias Section -->
    <div class="bg-white py-20">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-2 bg-blue-100 text-blue-600 rounded-full text-sm font-bold uppercase tracking-wide mb-4">
            Actualidad
          </span>
          <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Noticias Recientes</h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">Mantente informado sobre los últimos acontecimientos de nuestra comunidad universitaria</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <article class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <div class="relative h-64 overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 transform group-hover:scale-110 transition-transform duration-500"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            <div class="p-6">
              <div class="flex items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-blue-600 font-semibold">1 de septiembre, 2024</span>
              </div>
              <h3 class="text-xl font-bold text-gray-800 mb-3 group-hover:text-blue-600 transition-colors">Nuevo Laboratorio de Robótica</h3>
              <p class="text-gray-600 mb-4">Inauguramos laboratorio de última generación para estudiantes de ingeniería con equipamiento de vanguardia.</p>
              <a href="#" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold group">
                Leer más
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </article>
          
          <article class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <div class="relative h-64 overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-br from-green-500 via-green-600 to-emerald-700 transform group-hover:scale-110 transition-transform duration-500"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="p-6">
              <div class="flex items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-green-600 font-semibold">28 de agosto, 2024</span>
              </div>
              <h3 class="text-xl font-bold text-gray-800 mb-3 group-hover:text-green-600 transition-colors">Convenio Internacional</h3>
              <p class="text-gray-600 mb-4">Firmamos acuerdo con universidades europeas para intercambio estudiantil y programas de investigación conjunta.</p>
              <a href="#" class="inline-flex items-center text-green-600 hover:text-green-800 font-semibold group">
                Leer más
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </article>
          
          <article class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <div class="relative h-64 overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-br from-purple-500 via-purple-600 to-indigo-700 transform group-hover:scale-110 transition-transform duration-500"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
            </div>
            <div class="p-6">
              <div class="flex items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-purple-600 font-semibold">25 de agosto, 2024</span>
              </div>
              <h3 class="text-xl font-bold text-gray-800 mb-3 group-hover:text-purple-600 transition-colors">Investigación Premiada</h3>
              <p class="text-gray-600 mb-4">Nuestros investigadores reciben premio nacional por proyecto innovador en ciencias de la salud y biotecnología.</p>
              <a href="#" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold group">
                Leer más
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            </div>
          </article>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white py-24 overflow-hidden">
      <!-- Decorative Elements -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-yellow-500 rounded-full filter blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl"></div>
      </div>
      
      <div class="relative max-w-4xl mx-auto px-4 text-center">
        <div class="inline-block px-4 py-2 bg-yellow-500/20 border border-yellow-500/30 text-yellow-300 rounded-full text-sm font-bold uppercase tracking-wide mb-6">
          Tu futuro comienza aquí
        </div>
        <h2 class="text-4xl md:text-5xl font-bold mb-6">¿Listo para comenzar tu futuro?</h2>
        <p class="text-xl md:text-2xl text-blue-100 mb-10 max-w-2xl mx-auto">Únete a nuestra comunidad académica y alcanza tus metas profesionales con la mejor educación.</p>
        <button 
          @click="handleInscripcion"
          :disabled="verificandoSolicitud"
          class="bg-yellow-500 hover:bg-yellow-400 text-blue-900 px-10 py-5 rounded-xl font-bold text-lg transition-all transform hover:scale-105 shadow-2xl disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-3"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span v-if="!verificandoSolicitud">Solicitar Admisión</span>
          <span v-else class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            Verificando...
          </span>
        </button>
        <p class="mt-6 text-blue-200 text-sm">
          Proceso 100% en línea • Respuesta en 48 horas • Sin costo de inscripción
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import api from '@/services/api'

const router = useRouter()
const auth = useAuthStore()
const stats = ref({ carreras: 0 })
const verificandoSolicitud = ref(false)

// Carrusel Hero
const currentSlide = ref(0)
let carouselInterval = null

const heroSlides = [
  {
    badge: 'Excelencia Académica',
    title: 'Bienvenido a la Universidad de Caracas',
    subtitle: 'Formando líderes con excelencia académica y compromiso social desde 1974',
    image: 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1920&q=80'
  },
  {
    badge: 'Innovación y Tecnología',
    title: 'Construye tu Futuro con Nosotros',
    subtitle: 'Laboratorios de vanguardia y tecnología de punta para tu formación profesional',
    image: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1920&q=80'
  },
  {
    badge: 'Comunidad Global',
    title: 'Conecta con el Mundo',
    subtitle: 'Convenios internacionales y programas de intercambio en más de 30 países',
    image: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1920&q=80'
  },
  {
    badge: 'Investigación de Impacto',
    title: 'Investiga y Transforma',
    subtitle: 'Proyectos de investigación que generan cambios positivos en la sociedad',
    image: 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?w=1920&q=80'
  }
]

function nextSlide() {
  currentSlide.value = (currentSlide.value + 1) % heroSlides.length
}

function previousSlide() {
  currentSlide.value = currentSlide.value === 0 ? heroSlides.length - 1 : currentSlide.value - 1
}

function startCarousel() {
  carouselInterval = setInterval(() => {
    nextSlide()
  }, 5000) // Cambiar cada 5 segundos
}

function stopCarousel() {
  if (carouselInterval) {
    clearInterval(carouselInterval)
  }
}

async function cargarStats() {
  try {
    const { data } = await axios.get('http://localhost:8000/api/public/carreras', {
      params: { per_page: 1 }
    })
    stats.value.carreras = data.total
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
    stats.value.carreras = 40 // Fallback
  }
}

/**
 * Maneja el clic en los botones de inscripción
 * Si el usuario está autenticado, va al wizard de admisión
 * Si no está autenticado, va al registro público
 */
async function handleInscripcion() {
  if (verificandoSolicitud.value) return // Evitar clicks múltiples
  
  if (auth.isAuthenticated && auth.userRole === 'estudiante') {
    // Usuario autenticado (estudiante) -> verificar si ya tiene solicitud
    verificandoSolicitud.value = true
    try {
      // Intentar obtener su solicitud actual
      const { data } = await api.get('/estudiante/admision/solicitud')
      
      if (data.solicitud && data.solicitud.estado !== 'borrador') {
        // Ya tiene solicitud enviada -> ir al dashboard para ver su estado
        router.push('/estudiante/dashboard')
      } else {
        // Solicitud en borrador o no existe -> ir al wizard
        router.push('/estudiante/admision')
      }
    } catch (error) {
      // Si hay error (ej: no autenticado correctamente), ir al wizard por defecto
      console.error('Error al verificar solicitud:', error)
      router.push('/estudiante/admision')
    } finally {
      verificandoSolicitud.value = false
    }
  } else if (auth.isAuthenticated) {
    // Usuario autenticado pero no es estudiante -> mostrar mensaje
    alert('Esta opción solo está disponible para estudiantes. Por favor, cierra sesión e intenta de nuevo.')
  } else {
    // Usuario no autenticado -> ir al registro público
    router.push('/registro')
  }
}

onMounted(() => {
  cargarStats()
  startCarousel()
})

onUnmounted(() => {
  stopCarousel()
})
</script>

<style scoped>
/* Animaciones */
@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-50px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-slideInLeft {
  animation: slideInLeft 0.8s ease-out forwards;
}

.animation-delay-100 {
  animation-delay: 0.1s;
  opacity: 0;
}

.animation-delay-200 {
  animation-delay: 0.2s;
  opacity: 0;
}

.animation-delay-300 {
  animation-delay: 0.3s;
  opacity: 0;
}

/* Transiciones del carrusel */
.fade-enter-active, .fade-leave-active {
  transition: opacity 1s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>