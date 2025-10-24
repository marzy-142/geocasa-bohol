# Methodology

## 4.1 Requirements Analysis and Requirements Documentation

In describing how the project is designed, the developers used a use-case diagram to describe how this project operates. The use case diagram illustrates the functional requirements of the GeoCasa Bohol Real Estate Management System by showing the interactions between different user roles (actors) and the various functionalities (use cases) they can perform within the system.

## Use Case Diagram

The GeoCasa Bohol system supports three primary user roles, each with specific responsibilities and permissions:

### Actors and Their Roles

**Admin** - System administrators who have complete control and oversight of the entire platform. Admins manage users, approve broker applications, moderate property listings, oversee transactions, generate reports, and maintain system integrity. They serve as the central authority ensuring smooth operation of the real estate management system.

**Broker** - Licensed real estate professionals who have been approved by administrators to operate on the platform. Brokers create and manage property listings, respond to client inquiries, facilitate transactions, schedule property viewings, communicate with clients, and earn commissions on successful sales. Brokers must undergo an application and verification process before gaining full system access.

**Client** - End users who utilize the platform to browse properties, save favorites, submit inquiries to brokers, schedule viewings, manage transactions, and communicate with their assigned brokers. Clients can also submit seller requests if they own properties they wish to list on the platform.

**Role scope clarification:** The system maintains only three authenticated roles: Admin, Broker, and Client (Buyer). There is no separate authenticated "Seller" role. Property owners who wish to list a property interact via the public Seller Request form as guests, or optionally as Clients if they choose to register; their requests can then be associated with their account for status viewing.

### System Use Cases

#### 1. User Registration and Authentication

**Actors:** Client, Broker

**Description:** This use case handles the onboarding process for new users joining the GeoCasa Bohol platform. Users can register as either clients or brokers, with brokers requiring additional verification and approval from administrators.

**Functionalities:**

-   Complete registration form with personal information (name, email, phone, address)
-   Select user role (Client or Broker)
-   Email verification through confirmation link
-   Set secure password meeting validation requirements
-   Upload profile photo
-   For Brokers: Upload professional credentials (PRC license, valid ID, professional documents)
-   For Brokers: Undergo admin verification and approval process
-   Accept terms of service and privacy policy
-   Phone number verification via SMS using Twilio integration

**Benefits:** Ensures proper user identification, maintains platform security, and establishes trust through verified broker credentials.

#### 2. Browse and Search Properties

**Actors:** Client, Broker, Public (Guest Users)

**Description:** This comprehensive use case enables users to discover properties through powerful search and filtering capabilities with interactive map integration.

**Functionalities:**

-   Search properties by location, price range, property type, and amenities
-   Filter by property status (available, sold, rented)
-   View properties on interactive Leaflet maps with location markers
-   Sort results by price, date listed, size, or relevance
-   View detailed property information including specifications, features, and pricing
-   Browse high-quality property photos in gallery view
-   Experience 360-degree virtual tours using Photo Sphere Viewer
-   View property location on map with nearby points of interest
-   Save searches for future reference with notification alerts
-   Compare multiple properties side-by-side
-   Track viewed properties in browsing history

**Benefits:** Provides comprehensive property discovery experience, helps users make informed decisions through detailed information and virtual tours, and saves time through advanced filtering.

#### 3. Save and Manage Favorite Properties

**Actors:** Client

**Description:** This use case allows clients to save properties they are interested in for easy access and future reference.

**Functionalities:**

-   Save properties to favorites/wishlist
-   Remove properties from favorites
-   View all saved properties in dedicated dashboard section
-   Receive notifications when saved property details change
-   Organize favorites for comparison
-   Track property availability status

**Benefits:** Helps clients organize their property search, track multiple options, and receive updates on properties of interest.

#### 4. Submit and Manage Property Inquiries

**Actors:** Client

**Description:** This use case enables clients to express interest in properties by submitting inquiries that are routed to the appropriate brokers for response.

**Functionalities:**

-   Submit inquiry forms for specific properties
-   Include personalized messages and questions
-   Automatically route inquiries to property-listed brokers
-   Track inquiry status (submitted, responded, in discussion)
-   View all submitted inquiries in client dashboard
-   Receive real-time notifications when brokers respond
-   Follow up on inquiries through messaging system

**Benefits:** Facilitates direct communication between interested clients and property brokers, streamlines the inquiry process, and ensures timely responses.

#### 5. Real-Time Messaging System

**Actors:** Broker, Client

**Description:** This use case provides a comprehensive real-time messaging platform using Laravel Reverb and WebSockets for instant communication between users.

**Functionalities:**

-   Send and receive instant messages
-   Create conversations related to specific properties or transactions
-   Attach files and images to messages
-   Receive real-time push notifications for new messages
-   View message read receipts and online status
-   Search through conversation history
-   Organize conversations by property or transaction
-   Access messaging across all devices

**Benefits:** Centralizes all communication within the platform, provides instant connectivity, creates auditable message trails, and eliminates need for external communication tools.

#### 6. Manage Property Listings (Broker)

**Actors:** Broker

**Description:** This use case covers the complete property listing management workflow for approved brokers, from creation to renewal.

**Functionalities:**

-   Create new property listings with detailed information
-   Upload multiple property photos (up to 10 images)
-   Upload 360-degree panoramic images for virtual tours
-   Set property location coordinates on interactive map
-   Specify property details (type, size, bedrooms, bathrooms, amenities)
-   Set pricing and payment terms
-   Choose property status (available, sold, rented)
-   Edit existing property listings
-   Delete or archive properties
-   Renew expired listings
-   Mark properties as featured (with admin approval)
-   Track property views and inquiry statistics
-   Monitor listing expiration dates

**Benefits:** Empowers brokers to effectively showcase properties, maintain accurate listings, and track performance metrics for their portfolio.

#### 7. Broker Application and Approval

**Actors:** Broker (Applicant), Admin (Approver)

**Description:** This use case manages the broker verification and approval workflow, ensuring only qualified professionals operate on the platform.

**Functionalities:**

-   Submit broker application with credentials
-   Upload required documents (PRC license, valid IDs, certificates)
-   Admin reviews application and documents
-   Admin verifies document authenticity
-   Admin approves or rejects application with reason
-   Applicant receives notification of decision
-   Rejected applicants can view rejection reasons
-   Approved brokers gain full system access
-   Pending applicants view application status

**Benefits:** Maintains platform credibility through verified professionals, protects clients from unlicensed operators, and ensures compliance with real estate regulations.

#### 8. Manage Users and Roles

**Actors:** Admin

**Description:** This use case enables administrators to manage all user accounts, roles, and permissions across the system.

**Functionalities:**

-   View all registered users (admins, brokers, clients)
-   Search and filter users by role, status, or registration date
-   Create new user accounts with specific roles
-   Edit user profiles and information
-   Suspend or reactivate user accounts
-   Delete user accounts (with proper authorization)
-   Assign or modify user roles
-   View user activity logs and statistics
-   Bulk actions for managing multiple users
-   Export user data for reporting

**Benefits:** Provides centralized user management, ensures proper access control, and maintains system security through comprehensive oversight.

#### 9. Approve and Moderate Property Listings

**Actors:** Admin

**Description:** This use case allows administrators to review and moderate property listings to ensure quality standards and prevent fraudulent or inappropriate content.

**Functionalities:**

-   View all property listings (pending, approved, declined)
-   Review property details, photos, and information
-   Verify property ownership and documentation
-   Approve properties meeting quality standards
-   Decline properties with issues (with detailed feedback)
-   Request additional information or corrections
-   Set property visibility status
-   Monitor property compliance with platform guidelines
-   Track moderation history and actions

**Benefits:** Maintains listing quality, prevents fraudulent properties, ensures user trust, and upholds platform reputation.

#### 10. Transaction Management

**Actors:** Admin, Broker, Client

**Description:** This comprehensive use case handles the complete real estate transaction lifecycle from initial inquiry to finalization.

**Functionalities:**

-   Create transactions from property inquiries
-   Track transaction stages (inquiry, viewing, offer, negotiation, contract, closing, finalized)
-   Update transaction status with detailed notes
-   Assign brokers to transactions
-   Set commission rates and amounts
-   Upload transaction documents (contracts, agreements, receipts)
-   Schedule meetings and property viewings
-   Record payment details and milestones
-   Client approval for transaction progression
-   Calculate broker commissions automatically
-   Generate transaction reports
-   Archive completed transactions

**Benefits:** Provides transparent transaction tracking, ensures all parties stay informed, facilitates document management, and accurately calculates commissions.

#### 11. Seller Request Submission (Guest or Client)

**Actors:** Client, Public (Guest Users)

**Description:** Property owners submit requests to list their properties via a publicly accessible form (no login required). If they later register or already have a Client account with the same email, their request can be linked to their dashboard for status viewing.

Note: When a Client logs in with the same email used for a guest submission, their Seller Requests appear in their dashboard (email-based association).

**Functionalities:**

-   Submit property listing request form
-   Provide property details and photos
-   Upload property documentation
-   Include contact information
-   Track request status
-   Admin reviews and assigns broker to seller request
-   Broker contacts seller to proceed with listing
-   Convert seller request to active property listing

**Benefits:** Simplifies property listing process for owners, expands platform inventory, and creates new business opportunities for brokers.

#### 12. Analytics and Reporting

**Actors:** Admin, Broker

**Description:** This use case provides comprehensive analytics dashboards and reporting capabilities for data-driven decision making.

**Functionalities:**

-   View system-wide statistics (Admin)
-   Monitor user growth trends
-   Track property listing metrics
-   Analyze transaction volumes and values
-   View broker performance rankings
-   Generate commission reports
-   Export data in multiple formats (CSV, PDF, Excel)
-   Visualize data using Chart.js charts and graphs
-   View monthly sales trends
-   Monitor inquiry-to-transaction conversion rates
-   Track top-performing properties and brokers

**Benefits:** Enables data-driven decisions, identifies trends and opportunities, measures broker performance, and provides business intelligence.

#### 13. Document Management

**Actors:** Admin, Broker, Client

**Description:** This use case manages the storage, organization, and sharing of transaction-related documents securely.

**Functionalities:**

-   Upload transaction documents
-   Categorize documents by type (contract, receipt, agreement, etc.)
-   View and download documents
-   Delete outdated documents
-   Share documents with relevant parties
-   Track document upload history
-   Verify document authenticity (Admin)
-   Secure storage with access control
-   Document versioning

**Benefits:** Centralizes document storage, ensures secure access, maintains transaction history, and facilitates compliance with legal requirements.

#### 14. Meeting and Viewing Scheduling

**Actors:** Broker, Client

**Description:** This use case facilitates scheduling of property viewings and client meetings with automated reminders.

**Functionalities:**

-   Schedule property viewing appointments
-   Set meeting dates, times, and locations
-   Invite participants to meetings
-   Send automated email/SMS reminders
-   View meeting calendar
-   Reschedule or cancel meetings
-   Track meeting history
-   Record meeting notes and outcomes
-   Confirm attendance

**Benefits:** Streamlines appointment scheduling, reduces no-shows through reminders, and improves coordination between brokers and clients.

#### 15. Notification Management

**Actors:** Admin, Broker, Client

**Description:** This use case manages real-time and scheduled notifications across multiple channels to keep users informed.

**Functionalities:**

-   Receive real-time browser notifications via Laravel Echo
-   Get SMS notifications via Twilio integration
-   Email notifications for important events
-   Notification for new inquiries (Broker)
-   Notification for inquiry responses (Client)
-   Transaction status update notifications
-   Message notifications
-   Meeting reminders
-   Property price change alerts
-   New property matching saved search criteria
-   Broker application status updates
-   Configure notification preferences

**Benefits:** Keeps users informed in real-time, ensures timely responses, reduces missed opportunities, and improves user engagement.

**Implementation Notes (SMS):**

-   SMS notifications are delivered via Twilio using Laravel's notification channel (NotificationChannels/Twilio).
-   The system sends SMS for urgent new inquiries and key transaction milestones when the user has a phone number and SMS preferences enabled.
-   Environment variables required: `TWILIO_SID`, `TWILIO_TOKEN`, and `TWILIO_FROM` (configured in `config/services.php`).
-   The notifiable phone number is resolved from each user's Notification Preferences (fallback to the user's `phone` field).

#### 16. Client-Broker Assignment

**Actors:** Admin

**Description:** This use case enables administrators to assign clients to specific brokers for personalized service and relationship management.

**Functionalities:**

-   View unassigned clients
-   Assign clients to available brokers
-   Bulk assign multiple clients
-   Reassign clients to different brokers
-   Track broker-client relationships
-   Monitor broker client load
-   View assignment history

**Benefits:** Ensures every client receives dedicated broker support, balances broker workload, and improves service quality through personalized attention.

#### 17. Broker Performance Tracking

**Actors:** Admin, Broker

**Description:** This use case tracks and displays broker performance metrics to incentivize excellence and identify top performers.

**Functionalities:**

-   Track total properties listed
-   Monitor active vs. sold properties
-   Calculate total transactions completed
-   Compute total commission earned
-   Display performance rankings (leaderboard)
-   View inquiry response rates
-   Track client satisfaction scores
-   Monitor average time to close deals
-   Generate broker performance reports

**Benefits:** Motivates brokers through competitive rankings, identifies top performers for recognition, and provides insights for improvement.

#### 18. Saved Search Alerts

**Actors:** Client

**Description:** This use case allows clients to save their search criteria and receive automatic notifications when new matching properties are listed.

**Functionalities:**

-   Save custom search filters
-   Name saved searches for easy identification
-   Enable/disable notifications for each saved search
-   Receive alerts when matching properties are listed
-   View all saved searches
-   Edit search criteria
-   Delete saved searches
-   Track search result counts

**Benefits:** Helps clients discover relevant properties automatically, saves time on repeated searches, and ensures they don't miss suitable properties.

#### 19. Property Renewal and Featured Listings

**Actors:** Broker, Admin

**Description:** This use case manages property listing renewals and featured/premium placement on the platform.

**Functionalities:**

-   Renew expired property listings
-   Mark properties as featured (requires approval)
-   Track listing expiration dates
-   Receive renewal reminders
-   Featured properties appear in prominent positions
-   Auto-feature functionality for premium listings
-   Track featured listing duration
-   Monitor featured listing performance

**Benefits:** Keeps listings current, provides premium exposure for key properties, and generates additional revenue through featured placements.

#### 20. System Activity Auditing

**Actors:** Admin

**Description:** This use case tracks all administrative actions and system changes for security, compliance, and accountability.

**Functionalities:**

-   Log all admin actions automatically
-   Record user modifications
-   Track property approvals/rejections
-   Monitor broker verifications
-   View activity by admin user
-   View activity by target entity
-   Export audit logs
-   Search audit trail
-   Generate compliance reports

**Benefits:** Ensures accountability, supports compliance requirements, enables security investigations, and provides complete system activity visibility.

## System Workflow and Interactions

The use case diagram demonstrates several key workflows within the GeoCasa Bohol system:

### Administrative Workflow

1. Admins manage the overall system by controlling users, roles, and property approvals
2. They review and moderate property listings to ensure quality standards
3. They oversee inquiries and transactions across the platform
4. They have access for monitoring.

### Broker Workflow

1. Brokers create and manage property listings in the inventory
2. They communicate with potential buyers through the messaging system
3. They schedule property viewings and manage client meetings
4. They process transactions and manage documents and milestones
5. They coordinate with administrators for property approvals

### Buyer/Client Workflow

1. Buyers create accounts and browse the system for available properties
2. They view property details, virtual tours, and location maps
3. They add properties to favorites/cart for comparison
4. They submit property inquiries and communicate with brokers

### Seller Workflow

1. Property owners submit a Seller Request via the public form or after logging in as a Client
2. Admin reviews the request and assigns an approved Broker to handle the listing
3. The assigned Broker verifies details (site visit, photos, documents) and creates a draft listing from the request
4. Admin moderates and approves the listing; once approved, the property is published
5. Owners receive updates via email/SMS and, if they have an account, can view the status of their seller request and published listing
6. Owners communicate with the assigned Broker about pricing, offers, and scheduling; the Broker handles buyer inquiries and negotiations on the owner's behalf

Note: The system does not maintain a separate "Seller" authenticated role. Property owners interact via a public Seller Request form as guests, or as Clients if they choose to register. Admin triages and assigns a Broker; the Broker verifies details and prepares the listing for Admin approval and publication.

## Development Methodology

The GeoCasa Bohol Real Estate Management System was developed using an **Agile iterative approach** with the following phases:

### Phase 1: Requirements Gathering and Analysis

-   Conducted stakeholder interviews with real estate brokers, property owners, and potential buyers
-   Analyzed existing real estate platforms to identify gaps and opportunities
-   Documented functional and non-functional requirements
-   Created use case diagrams and user stories
-   Defined system scope and success criteria

### Phase 2: System Design

-   Designed database schema with entity-relationship diagrams
-   Created wireframes and mockups for user interfaces
-   Defined system architecture using Laravel MVC pattern
-   Planned API endpoints and data flow
-   Designed real-time communication infrastructure
-   Established security protocols and access control mechanisms

### Phase 3: Implementation and Development

-   Set up development environment with XAMPP, VS Code, and version control
-   Implemented backend functionality using Laravel framework
-   Developed frontend interfaces using Vue.js and Tailwind CSS
-   Integrated third-party services (Twilio, Leaflet Maps, Photo Sphere Viewer)
-   Implemented real-time features using Laravel Reverb and Echo
-   Created admin dashboard with analytics and reporting
-   Developed broker portal with property and inquiry management
-   Built buyer interface with search, filtering, and virtual tours

### Phase 4: Testing and Quality Assurance

-   Conducted unit testing using PHPUnit and Vitest
-   Performed integration testing for all modules
-   Executed user acceptance testing (UAT) with stakeholders
-   Tested responsive design across multiple devices
-   Validated security measures and penetration testing
-   Performance testing and optimization
-   Cross-browser compatibility testing

### Phase 5: Deployment and Maintenance

-   Configured production server environment
-   Migrated database and application to production
-   Implemented backup and disaster recovery procedures
-   Monitored system performance using Sentry
-   Provided user training and documentation
-   Established support procedures for ongoing maintenance
-   Planned iterative improvements based on user feedback

## Use Case Specifications

Each use case in the system follows a structured specification format:

**Use Case Name:** Descriptive name of the functionality

**Actors:** User roles that can perform this action

**Preconditions:** System state required before the use case can execute

**Main Flow:** Step-by-step description of the normal execution path

**Alternative Flows:** Variations and exceptions in the execution

**Postconditions:** System state after successful execution

**Business Rules:** Constraints and validations applied

This structured approach ensures comprehensive coverage of all system functionalities and provides clear guidance for implementation and testing.

## System Integration Points

The use case diagram reveals several integration points where different actors and use cases interact:

1. **User Management Integration:** Admin roles integrate with all system functions to maintain oversight and control

2. **Property Lifecycle Integration:** Properties flow through creation (Broker) → approval (Admin) → browsing (Client) → inquiry and client assignment (Admin/Broker) → transaction completion

3. **Communication Integration:** Messaging connects all actors, enabling coordination throughout property transactions

4. **Payment Integration:** Financial transactions link clients, brokers, and administrators in the sales process (with broker commission tracking)

5. **Notification Integration:** All use cases trigger real-time notifications to relevant parties, ensuring timely responses and actions

This comprehensive methodology ensures that the GeoCasa Bohol Real Estate Management System meets all stakeholder requirements while maintaining scalability, security, and user-friendliness.
