@extends('index')

@section('main-content')
    <div>
        {{-- APP BAR --}}
        <div class="h-72 bg-teal-400 shadow-xl">
            <div class="relative flex justify-between px-8 text-white">
                <div class="absolute top-4 left-40 text-xl px-3 py-1 text-black bg-yellow-300">
                    {{ $user->full_name }}
                </div>
                <a href="{{ route('logout') }}">
                    <div class="absolute right-5 top-4 bg-gray-300 px-3 py-1 text-black hover:bg-gray-400">
                        Log out
                    </div>
                </a>
            </div>
            <div class="flex flex-col justify-center items-center h-full text-6xl text-center">
                <div>
                    Selamat Datang Di Aplikasi
                </div>
                <div>
                    Absensi Guru Honorer
                </div>
            </div>
        </div>

        {{-- Deksripsi Sekolah --}}
        <div class="flex flex-col justify-center mt-8">
            <div class="text-center font-medium text-xl uppercase">
                SMP N 2 Telukjambe Barat
            </div>
            <hr class="w-48 h-1 mx-auto my-4 bg-black border-0 rounded">
            <div class="grid grid-cols-2 px-8 gap-8">
                <div>
                    <div class="font-medium">
                        Galeri
                    </div>
                    <div id="default-carousel" class="relative w-full mb-8" data-carousel="slide">
                        <!-- Carousel wrapper -->
                        <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
                            <!-- Item 1 -->
                            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                                <img src="/galeri/1.jpg"
                                    class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                                    alt="...">
                            </div>
                            <!-- Item 2 -->
                            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                                <img src="/galeri/2.jpg"
                                    class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                                    alt="...">
                            </div>
                            <!-- Item 3 -->
                            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                                <img src="/galeri/3.jpg"
                                    class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                                    alt="...">
                            </div>
                        </div>
                        <!-- Slider indicators -->
                        <div class="absolute z-30 flex -translate-x-1/2 bottom-5 left-1/2 space-x-3 rtl:space-x-reverse">
                            <button type="button" class="w-3 h-3 rounded-full" aria-current="true" aria-label="Slide 1"
                                data-carousel-slide-to="0"></button>
                            <button type="button" class="w-3 h-3 rounded-full" aria-current="false" aria-label="Slide 2"
                                data-carousel-slide-to="1"></button>
                            <button type="button" class="w-3 h-3 rounded-full" aria-current="false" aria-label="Slide 3"
                                data-carousel-slide-to="2"></button>
                        </div>
                        <!-- Slider controls -->
                        <button type="button"
                            class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                            data-carousel-prev>
                            <span
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                                <svg class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="M5 1 1 5l4 4" />
                                </svg>
                                <span class="sr-only">Previous</span>
                            </span>
                        </button>
                        <button type="button"
                            class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                            data-carousel-next>
                            <span
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                                <svg class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 9 4-4-4-4" />
                                </svg>
                                <span class="sr-only">Next</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div>
                    <div class="font-medium">
                        Profil Sekolah
                    </div>
                    <div>
                        <div>
                            SMPN 2 Teluk Jambe Barat merupakan salah satu sekolah menengah pertama yang berada di Karawang,
                            tepatnya berada di Wanasari, Telukjambe Barat, Karawang, Jawa Barat.
                        </div>
                        <div>
                            Berikut ini adalah identitas lengkap SMPN 2 Telukjambe Barat:
                        </div>
                        <ul class="ml-4">
                            <li><span class="font-medium"> NPSN :</span> 20217861</li>
                            <li><span class="font-medium"> Status :</span> Negeri</li>
                            <li><span class="font-medium"> Bentuk Pendidikan :</span> SMP</li>
                            <li><span class="font-medium"> Status Kepemilikan :</span> Pemerintah Pusat</li>
                            <li><span class="font-medium"> SK Pendirian Sekolah :</span> 13a//O/198</li>
                            <li><span class="font-medium"> Tanggal SK Pendirian :</span> 1998-01-29</li>
                            <li><span class="font-medium"> SK Izin Operasional :</span> 421/Kep.251-Huk/2008</li>
                            <li><span class="font-medium"> Tanggal SK Izin Operasional :</span> 2008-03-25</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
