<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <v-app id="app">
        <v-navigation-drawer
            v-model="drawer"
            app
        >
            <v-list dense nav>
                <v-list-item>
                    <v-list-item-content>
                        <v-list-item-title class="title">
                            USA
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            Manager Portal
                        </v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>

                <v-divider></v-divider>
                @if(Auth::check())
                <v-list-item link href="{{ route('userIndex') }}">
                    <v-list-item-icon>
                        <v-icon>mdi-view-dashboard</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                        <v-list-item-title>{{ __('My Dashboard') }}</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item link href="{{ route('vacationRequest') }}">
                    <v-list-item-action>
                        <v-icon>mdi-calendar-plus</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>{{ __('New Vacation Request') }}</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>

                <v-divider></v-divider>

                    @if(Auth::user()->is_admin)
                    <v-list-item link href="{{ route('adminIndex') }}">
                        <v-list-item-icon>
                            <v-icon>mdi-police-badge</v-icon>
                        </v-list-item-icon>
                        <v-list-item-content>
                            <v-list-item-title>{{ __('Admin Home') }}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item link href="{{ route('userMgmtIndex') }}">
                        <v-list-item-action>
                            <v-icon>mdi-account-supervisor-circle</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>{{ __('User Management') }}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    @endif
                <v-divider></v-divider>
                @endif

                @if(Auth::check())
                <v-list-item link @click.prevent="logout">
                    <v-list-item-icon>
                        <v-icon>mdi-logout</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                        <v-list-item-title>{{ __('Logout ' . Auth::user()->first_name . ' ' . Auth::user()->last_name ) }}</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                @else
                <v-list-item link href="{{ route('login') }}">
                    <v-list-item-icon>
                        <v-icon>mdi-login</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                        <v-list-item-title>{{ __('Login') }}</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                @endif
            </v-list>
        </v-navigation-drawer>

        <v-app-bar
            app
            color="primary darken-2"
            dark
        >
            <v-app-bar-nav-icon @click.stop="drawer = !drawer" v-if="$vuetify.breakpoint.mdAndDown"></v-app-bar-nav-icon>
            <a href="{{ url('/dashboard') }}"><img class="mr-3" src="{{ url('/images/usa-logo.jpg') }}" alt="Union Sorters of America Logo" /></a>
{{--            <v-toolbar-title><v-btn href="{{ url('/dashboard') }}" text>{{ config('app.name', 'Union Sorters Manager Portal') }}</v-btn></v-toolbar-title>--}}
            <template v-if="$vuetify.breakpoint.lgAndUp">
                <v-toolbar-items>
                    @if(Auth::check())
                        @if(Auth::user()->is_admin)
                            <v-btn href="{{ route('adminIndex') }}" text>{{ __('Admin Home') }}</v-btn>
                            <v-btn href="{{ route('userMgmtIndex') }}" text>{{ __('User Management') }}</v-btn>
                        @endif
                        <v-btn href="{{ route('userIndex') }}" text>{{ __('My Dashboard') }}</v-btn>
                        <v-btn href="{{ route('vacationRequest') }}" text>{{ __('New Vacation Request') }}</v-btn>
                    @endif
                </v-toolbar-items>
                <v-spacer></v-spacer>
                <v-toolbar-items>
                    @guest
                        <v-btn href="{{ route('login') }}" text>{{ __('Login') }}</v-btn>
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
            </template>
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
            color="primary darken-2"
            app
        >
            <span class="white--text">&copy; {{ date('Y') }} <a href="https://unionsorters.com" target="_blank" dark class="white--text">Union Sorters of America</a></span>
        </v-footer>
    </v-app>
</body>
</html>
