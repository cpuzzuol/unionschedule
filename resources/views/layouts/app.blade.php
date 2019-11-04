<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <v-app id="app">
        <v-navigation-drawer
            v-model="drawer"
            app
        >
            <v-list dense>
                <v-list-item @click="">
                    <v-list-item-action>
                        <v-icon>mdi-home</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Home</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item @click="">
                    <v-list-item-action>
                        <v-icon>mdi-contact-mail</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Contact</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar
            app
            color="red darken-2"
            dark
        >
            <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
            <v-toolbar-title><v-btn href="{{ url('/') }}" text>{{ config('app.name', 'Laravel') }}</v-btn></v-toolbar-title>
            <v-spacer></v-spacer>
            <v-toolbar-items>
                @guest
                    <v-btn href="{{ route('login') }}" text>{{ __('Login') }}</v-btn>
                    @if (Route::has('register'))
                        <v-btn href="{{ route('register') }}" text>{{ __('Register') }}</v-btn>
                    @endif
                @else
                    <v-menu offset-y>
                        <template v-slot:activator="{ on }">
                            <v-btn
                                text
                                v-on="on"
                            >
                                <v-icon>mdi-account</v-icon> {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
                            </v-btn>
                        </template>
                        <v-list>
                            <v-list-item>
                                <v-list-item-title>
                                    <v-btn text @click.prevent="logout">{{ __('Logout') }}</v-btn>
                                </v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                @endguest
            </v-toolbar-items>
        </v-app-bar>

        <v-content>
            <v-container fluid>
                <!-- CONTENT GOES HERE -->
                @yield('content')
            </v-container>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </v-content>
        <v-footer
            color="red darken-2"
            app
        >
            <span class="white--text">&copy; {{ date('Y') }} <a href="https://unionsorters.com" target="_blank" dark class="white--text">Union Sorters of America</a></span>
        </v-footer>
    </v-app>
</body>
</html>
