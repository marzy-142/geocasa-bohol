# 3.2 Details of Technology Used

This section introduces the technologies used to utilize and develop the proposed GeoCasa Bohol Real Estate Management System.

## Development Environment

**Microsoft Visual Studio Code** - As freeware from Microsoft, it helps computer programmers to develop their software. It is utilized in creating defaulting computer programs, websites, web applications, web services, and even mobile applications. It also allows programmers to develop applications and websites using platforms like Visual Basic, C++, C++/CLI, C#, JavaScript, TypeScript, XML, XSL, HTML, CSS, and many more. In this project, VS Code serves as the primary integrated development environment (IDE) for both frontend and backend development.

## Server & Development Tools

**XAMPP** - The developers will use this as a developing tool, so web programmers can be able to test their development on their computers but offline and have no relation with the Internet. XAMPP provides a complete local server environment including Apache web server, MySQL database, PHP interpreter, and phpMyAdmin for database management. This allows developers to build and test the entire application stack locally before deployment.

**Composer** - is a dependency management tool for PHP that allows developers to declare and manage the libraries their project depends on. Composer handles downloading and installing PHP packages from Packagist (the main Composer repository) and manages autoloading of classes. In this project, Composer manages all Laravel framework dependencies and third-party PHP libraries, ensuring consistent versions across development and production environments.

**Node.js & NPM** - Node.js is a JavaScript runtime built on Chrome's V8 JavaScript engine that allows developers to run JavaScript on the server side. NPM (Node Package Manager) is the default package manager for Node.js and manages JavaScript dependencies. In this project, NPM is used to install and manage frontend libraries, build tools, and development dependencies required for the Vue.js and Vite-based frontend architecture.

## Database Management

**MySQL** - is a relational database that is managed through a server. MySQL can be used in a small application and also in a large one. MySQL is fast, accurate, and among those databases that require little or no input from the user. MySQL uses standard SQL (Structured Query Language). In the GeoCasa Bohol system, MySQL stores all application data including user accounts, property listings, transactions, inquiries, broker information, client details, and system configurations. The database is designed with normalized tables to ensure data integrity and efficient querying.

**phpMyAdmin** - is a free and open-source administration tool for MySQL and MariaDB databases. Written in PHP, it provides a web-based interface for managing databases, tables, columns, relations, indexes, users, permissions, and more. The developers use phpMyAdmin to create database schemas, run SQL queries, import/export data, and monitor database performance during development and testing phases.

## Backend Framework & Core Technologies

**PHP 8.2** - is a widely-used open-source server-side scripting language especially suited for web development. PHP 8.2 introduces significant performance improvements, new features like readonly classes, enum improvements, and enhanced type system capabilities. The GeoCasa Bohol system leverages PHP 8.2's modern syntax, improved error handling, and performance optimizations to create a robust and maintainable backend application.

**Laravel Framework 11.31** - is mainly used for the development of custom PHP-based web applications. It is an application framework that can solve problems that are not very efficient when you are building an application the manual way, like providing the way your URLs will be structured, providing an engine to generate HTML (Hypertext Markup Language), and built-in support for authenticating users right out of the box. Laravel follows the MVC (Model-View-Controller) architectural pattern and includes features such as Eloquent ORM for database operations, Blade templating engine, authentication scaffolding, job queues, scheduled tasks, and comprehensive testing support. In this project, Laravel serves as the foundation providing routing, database abstraction, security features, and API endpoints.

**Laravel Breeze** - is an official Laravel starter kit that provides minimal and simple authentication scaffolding. It includes pre-built authentication views, controllers, and routes for login, registration, password reset, email verification, and profile management. Laravel Breeze is designed to be a starting point that developers can customize to fit their application needs. In the GeoCasa Bohol system, Breeze provides the foundation for user authentication with support for multiple user roles including administrators, brokers, sellers, and buyers.

**Laravel Sanctum** - is a lightweight authentication system for SPAs (Single Page Applications), mobile applications, and simple token-based APIs. It provides a simple way to authenticate users and generate API tokens for authentication without the complexity of OAuth. In this project, Sanctum handles API authentication for AJAX requests and ensures secure communication between the frontend and backend.

**Inertia.js** - is a modern approach to building classic server-driven web applications that combines the best of traditional server-rendered applications with the smooth user experience of single-page applications. Inertia allows developers to create fully client-side rendered, single-page apps without the complexity of building a separate API. It works as a glue between the Laravel backend and Vue.js frontend, enabling developers to use server-side routing and controllers while leveraging client-side rendering for fast, seamless page transitions without full page reloads.

## Frontend Framework & UI Technologies

**Vue.js 3.5** - is a progressive JavaScript framework for building user interfaces. Unlike other monolithic frameworks, Vue is designed from the ground up to be incrementally adoptable. Vue 3 introduces the Composition API, improved TypeScript support, better performance, and smaller bundle sizes. In the GeoCasa Bohol system, Vue.js powers all interactive user interfaces including property listings, search filters, real-time notifications, transaction management, and dashboard analytics. The component-based architecture allows for reusable UI elements and maintainable code.

**Tailwind CSS** - is a utility-first CSS framework, according to its creators. Tailwind is focused on how the item should be shown rather than the functionality of the item being designed. This makes it easy for the developer to experiment with different styles and layouts. Unlike traditional CSS frameworks that provide pre-designed components, Tailwind provides low-level utility classes that can be composed to build custom designs. The GeoCasa Bohol system uses Tailwind CSS to create a consistent, modern, and responsive design across all pages, with custom color schemes, typography, spacing, and animations configured in the tailwind.config.js file.

**Headless UI** - is a completely unstyled, fully accessible UI component library for Vue.js and React, designed to integrate beautifully with Tailwind CSS. It provides interactive components like dropdowns, modals, tabs, and transitions without imposing any styling decisions, allowing developers to fully customize the appearance using Tailwind utilities. In this project, Headless UI components ensure that complex interactive elements like selection menus, dialog boxes, and disclosure panels are accessible to all users including those using screen readers and keyboard navigation.

**Heroicons** - is a set of free, open-source SVG icons designed by the creators of Tailwind CSS. The library provides beautiful, hand-crafted icons in both outline and solid styles. Heroicons are used throughout the GeoCasa Bohol system for navigation menus, action buttons, status indicators, form inputs, and informational displays, creating a consistent visual language across the application.

## Build Tools & Asset Compilation

**Vite** - is a modern frontend build tool that provides an extremely fast development server with instant hot module replacement (HMR) and optimized production builds. Unlike traditional bundlers like Webpack, Vite serves source code over native ES modules during development, resulting in faster startup times and instant updates. For production, Vite bundles code using Rollup with extensive optimizations. In the GeoCasa Bohol system, Vite compiles Vue components, processes Tailwind CSS, optimizes assets, and provides development features like automatic page refresh on code changes.

**PostCSS** - is a tool for transforming CSS with JavaScript plugins. It can lint CSS, add vendor prefixes, use future CSS syntax, and perform many other transformations. In this project, PostCSS works with Tailwind CSS and Autoprefixer to process stylesheets, ensuring cross-browser compatibility and optimizing the final CSS output.

**Autoprefixer** - is a PostCSS plugin that parses CSS and adds vendor prefixes to CSS rules using values from Can I Use. It automatically adds browser-specific prefixes like -webkit-, -moz-, -ms-, and -o- to ensure that modern CSS features work across all browsers. This eliminates the need for developers to manually write vendor prefixes and ensures consistent styling across different browsers.

## Real-Time Communication Technologies

**Laravel Reverb** - is Laravel's official first-party WebSocket server that provides blazingly fast and scalable real-time communication for Laravel applications. Reverb handles WebSocket connections, manages channel subscriptions, and broadcasts events to connected clients. In the GeoCasa Bohol system, Reverb powers real-time features such as instant notification delivery when brokers respond to inquiries, live updates to transaction statuses, and real-time messaging between clients and brokers.

**Laravel Echo** - is a JavaScript library that makes it easy to subscribe to channels and listen for events broadcast by Laravel. Echo provides a simple, elegant API for consuming WebSocket events on the frontend, with support for presence channels (knowing who is online) and private channels (authenticated subscriptions). In this project, Echo connects the Vue.js frontend to the Reverb WebSocket server, enabling components to react instantly to server-side events.

**Pusher Protocol** - provides the communication protocol and client libraries used by Laravel Reverb. Pusher.js on the client side and pusher-php-server on the backend enable standardized WebSocket communication with features like automatic reconnection, fallback transports, and encrypted connections. The GeoCasa Bohol system uses these libraries to ensure reliable real-time communication even on networks with restrictive firewalls.

## Mapping & Location Technologies

**Leaflet.js** - is an open-source JavaScript library for mobile-friendly interactive maps. It is lightweight yet feature-rich, providing all the mapping features most developers need. Leaflet works efficiently across all major desktop and mobile platforms, supporting panning, zooming, markers, popups, and custom overlays. In the GeoCasa Bohol system, Leaflet displays property locations on interactive maps, allows users to explore properties geographically, shows boundaries of Bohol island, and provides directions to property locations. The system includes custom map markers for different property types and interactive popups showing property details.

**OpenStreetMap** - is a collaborative project to create free, editable map data of the world. OpenStreetMap provides the map tiles (background map images) used by Leaflet in the GeoCasa Bohol system. Unlike commercial mapping services, OpenStreetMap is free to use without API keys or usage limits, making it ideal for applications requiring extensive mapping functionality.

## Virtual Tour & 360° Viewing

**Photo Sphere Viewer** - is a JavaScript library that enables the display of 360-degree panoramic images and videos. It provides an immersive viewing experience where users can look around in all directions by dragging with mouse or touch, using device orientation on mobile devices, or through virtual reality headsets. The core library handles panorama rendering, user interactions, and smooth animations. In the GeoCasa Bohol system, Photo Sphere Viewer allows property sellers to upload 360-degree photos of their properties, giving potential buyers an immersive virtual tour experience where they can explore rooms and spaces as if they were physically present.

**Photo Sphere Viewer Markers Plugin** - extends the core Photo Sphere Viewer by adding the ability to place interactive markers (hotspots) within 360-degree panoramas. These markers can be clicked to display information, trigger actions, or navigate between different panoramas. In this project, markers are used to highlight important property features within virtual tours, such as pointing out upgraded appliances, architectural details, or transitions to different rooms, enhancing the virtual property viewing experience.

## Data Visualization

**Chart.js** - is a simple yet flexible JavaScript charting library for designers and developers. It provides eight different chart types including line, bar, radar, doughnut, pie, polar area, bubble, and scatter charts. Charts are responsive, animated, and customizable with extensive configuration options. In the GeoCasa Bohol system, Chart.js powers the analytics dashboards for administrators and brokers, visualizing key metrics such as monthly sales trends, property listings by type, commission earnings over time, top-performing brokers, transaction status distributions, and client acquisition rates.

## Routing & Navigation

**Ziggy** - is a JavaScript library that brings Laravel's named routes to the frontend. It generates a JavaScript object containing all route definitions from Laravel's routing files, allowing developers to use the same route() helper function in JavaScript that they use in PHP. This eliminates hardcoded URLs in frontend code and ensures that URL changes in Laravel routes automatically propagate to the frontend. In the GeoCasa Bohol system, Ziggy enables type-safe navigation in Vue components using Laravel route names instead of manual URL construction.

## Communication & Notifications

**Twilio** - is a cloud communications platform that provides APIs for sending and receiving SMS messages, making voice calls, and video communications. Twilio's programmable SMS API allows applications to send text messages to users' mobile phones worldwide. In the GeoCasa Bohol system, Twilio sends SMS notifications to brokers when they receive new property inquiries, alerts clients about transaction status updates, and delivers verification codes during user registration and password reset processes.

**Laravel Notification Channels** - provides a clean, simple API for sending notifications across various channels including mail, SMS, Slack, and database storage. The Twilio notification channel integrates Twilio's SMS capabilities into Laravel's notification system, allowing developers to send SMS notifications using the same elegant syntax as email notifications. This abstraction makes it easy to send multi-channel notifications (email + SMS) from a single notification class.

## Error Monitoring & Debugging

**Sentry** - is an error tracking and performance monitoring platform that helps developers identify, diagnose, and fix errors in real-time. Sentry captures exceptions, logs, and performance data from applications and provides detailed error reports including stack traces, breadcrumbs showing actions leading to the error, affected users, and environment context. The GeoCasa Bohol system integrates Sentry to automatically capture and report backend errors, JavaScript exceptions, and performance issues, enabling developers to quickly identify and resolve problems before they affect many users.

**Laravel Pail** - is a command-line tool that provides real-time log viewing for Laravel applications. It displays application logs in the terminal with syntax highlighting, filtering capabilities, and the ability to follow logs as they're written. During development and debugging, developers use Pail to monitor application behavior, track database queries, observe job processing, and troubleshoot issues without constantly opening log files.

## HTTP Client & AJAX

**Axios** - is a promise-based HTTP client for the browser and Node.js. It provides a simple and consistent API for making HTTP requests with features like request and response interception, automatic JSON transformation, timeout handling, and CSRF protection. In the GeoCasa Bohol system, Axios handles all AJAX requests from the Vue.js frontend to Laravel backend APIs, including form submissions, data fetching, file uploads, and real-time search queries. The axios instance is pre-configured with CSRF token headers for security.

## Date & Time Handling

**date-fns** - is a modern JavaScript date utility library providing comprehensive and consistent functions for parsing, validating, manipulating, and formatting dates. Unlike alternatives like Moment.js, date-fns is modular (import only what you need), immutable (functions return new date objects), and uses native Date objects. In the GeoCasa Bohol system, date-fns formats transaction dates, calculates time differences for "posted 3 hours ago" displays, handles date comparisons for filtering, and ensures consistent date formatting across different locales.

## Testing Frameworks

**PHPUnit** - is the de facto standard unit testing framework for PHP applications. It provides assertions for testing expected outcomes, mock objects for isolating code under test, database testing utilities, and test coverage analysis. The GeoCasa Bohol system includes comprehensive PHPUnit tests covering models, controllers, services, authentication, authorization, and business logic, ensuring code reliability and preventing regressions.

**Vitest** - is a blazing-fast unit test framework powered by Vite. It provides a Jest-compatible API with instant startup time, parallel test execution, and integrated code coverage reporting. Vitest is designed specifically for Vite projects, sharing the same configuration and plugins. In this project, Vitest tests Vue components, JavaScript utilities, composables, and services, ensuring frontend code reliability.

**Vue Test Utils** - is the official testing utility library for Vue.js. It provides methods for mounting Vue components in isolation, triggering user interactions, and asserting component behavior and output. Combined with Vitest, Vue Test Utils enables thorough testing of the GeoCasa Bohol frontend components including user registration forms, property search filters, and dashboard widgets.

**Testing Library** - provides simple and complete testing utilities that encourage good testing practices. Rather than testing implementation details, Testing Library focuses on testing components as users interact with them. In the GeoCasa Bohol project, Testing Library queries components by accessible labels and roles, ensuring tests remain maintainable even as implementation changes.

**JSDOM** - is a JavaScript implementation of web standards that runs in Node.js, providing a simulated browser environment for testing. It implements the DOM, HTML, and related web APIs, allowing tests to interact with components as if they were running in a real browser. Vitest uses JSDOM to execute Vue component tests in the Node.js environment during development.

## Code Quality & Development Tools

**Laravel Pint** - is an opinionated PHP code style fixer built on PHP-CS-Fixer. Pint automatically formats PHP code according to Laravel's coding standards, ensuring consistent code style across the entire project. It fixes indentation, spacing, bracket placement, and other stylistic issues without manual intervention.

**Faker** - is a PHP library that generates fake data for testing purposes. It can create realistic names, addresses, phone numbers, email addresses, dates, lorem ipsum text, and much more. In the GeoCasa Bohol system, Faker populates the database with realistic test data during development, including fake property listings, user accounts, and transactions.

**Mockery** - is a simple yet flexible PHP mock object framework for use in unit testing. It allows developers to create mock objects that simulate the behavior of real objects, enabling isolated testing of code units without external dependencies. The GeoCasa Bohol test suite uses Mockery to mock services, repositories, and external APIs.

**Collision** - is a detailed and intuitive error handler for console/command-line PHP applications. It provides beautiful error and exception reporting in the terminal with syntax highlighting, readable stack traces, and helpful context. Collision is integrated into Laravel to enhance the developer experience when running Artisan commands, tests, or queue workers.

## Security Technologies

**CSRF Protection** - Cross-Site Request Forgery (CSRF) protection is built into Laravel and protects against malicious attacks where unauthorized commands are transmitted from a user's trusted browser. Every form in the GeoCasa Bohol system includes a CSRF token that must be validated on the server, ensuring requests originate from legitimate application pages.

**Bcrypt Hashing** - is a password hashing function based on the Blowfish cipher. Laravel uses Bcrypt with configurable rounds (work factor) to securely hash user passwords. The GeoCasa Bohol system stores only bcrypt-hashed passwords, never plaintext, protecting user credentials even if the database is compromised.

**SQL Injection Prevention** - Laravel's Eloquent ORM and Query Builder automatically protect against SQL injection attacks by using parameterized queries. All user input is properly escaped and bound to SQL parameters, preventing malicious SQL code from being executed.

**XSS Protection** - Cross-Site Scripting (XSS) protection is built into Laravel's Blade templating engine and Vue.js framework. User-generated content is automatically escaped when rendered, preventing malicious scripts from executing in other users' browsers.

## Version Control & Collaboration

**Git** - is a distributed version control system that tracks changes in source code during software development. Git enables multiple developers to work on the GeoCasa Bohol project simultaneously, maintains complete history of all changes, allows branching for feature development, and facilitates code review through pull requests.

**GitHub** - is a web-based platform that uses Git for version control and adds collaboration features like pull requests, issue tracking, project boards, and continuous integration. The GeoCasa Bohol repository is hosted on GitHub, providing centralized code storage, backup, and team collaboration capabilities.

## Hardware Requirements

**Personal Computer (PC)** - A personal computer or PC is an inexpensive, multipurpose machine developed to meet the needs of a lone user. For development of the GeoCasa Bohol system, developers require PCs with sufficient processing power and memory to run XAMPP, multiple terminal sessions, VS Code, browsers for testing, and local database servers simultaneously. Minimum recommended specifications include Intel Core i5 or equivalent processor, 8GB RAM, 256GB storage, and a modern operating system (Windows 10/11, macOS, or Linux).

**Smartphone** - A smartphone is an advanced and flexible communication device comparable to a mobile phone but has similar functions to a computer. While compared to other feature phones to be found in the market, smartphones may not be as old, they boast a much better hardware base and complicated software. The features these systems enable include the ability to execute a range of applications, access the Internet, browse the Web via built-in mobile broadband, listen to music, watch videos, view photographs, play games, conventional voice calls and text messages. The GeoCasa Bohol system is designed to be fully responsive and mobile-friendly, allowing users to browse properties, submit inquiries, manage transactions, and receive notifications on smartphones. The system supports both iOS and Android devices through responsive web design.

**Web Browser** - Modern web browsers including Google Chrome, Mozilla Firefox, Microsoft Edge, and Safari are required to access the GeoCasa Bohol system. The application leverages modern web technologies including ES6 JavaScript, CSS Grid, Flexbox, and WebSocket connections, requiring up-to-date browser versions for optimal performance and security.

**Internet Connection** - A stable internet connection is required for accessing the GeoCasa Bohol system. For optimal performance, a broadband connection with minimum 5 Mbps download speed is recommended to support real-time features, map loading, 360-degree image viewing, and smooth page navigation.

## Development Workflow Tools

**Concurrently** - is a utility that runs multiple commands concurrently in parallel. In the GeoCasa Bohol project, the development script uses Concurrently to simultaneously run the Laravel development server (php artisan serve), queue worker (php artisan queue:listen), log viewer (php artisan pail), and Vite development server (npm run dev) in a single terminal window with color-coded output.

**Laravel Artisan** - is Laravel's command-line interface that provides helpful commands for common tasks. Artisan commands are used throughout development for generating boilerplate code, running migrations, seeding databases, clearing caches, running tests, managing queues, and performing maintenance tasks. Custom Artisan commands can be created for project-specific operations.

**Laravel Tinker** - is a powerful REPL (Read-Eval-Print Loop) for Laravel that allows developers to interact with the application directly from the command line. Tinker can query the database using Eloquent, test methods, inspect objects, and experiment with code without building test pages or APIs. It's invaluable for debugging and quick experimentation during development.

**Laravel Sail** - is a light-weight command-line interface for interacting with Laravel's default Docker development environment. Sail provides a complete development environment in Docker containers, including PHP, MySQL, Redis, and other services, ensuring consistent development environments across different machines and operating systems.
