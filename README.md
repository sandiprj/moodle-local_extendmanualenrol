# Manual Enrolment Extension Plugin

This plugin allows students to request extensions for their manual course enrolments and enables teachers/managers to approve or deny these requests.

## Features

- Students can request extensions for their manual course enrolments
- Students can specify the number of days needed and provide a reason
- Teachers and managers can approve or deny extension requests
- Automatic update of enrolment end dates upon approval
- Integration with Moodle's course navigation
- Role-based access control

## Requirements

- Moodle 4.1 or later
- Manual enrolment must be enabled in the course

## Installation

1. Place the plugin files in `/local/extendmanualenrol`
2. Visit your Moodle site's administration area
3. Follow the plugin installation process
4. The plugin will create necessary database tables automatically

## Usage

### For Students
1. Navigate to a course where you are enrolled via manual enrolment
2. Click on "Request Extension" in the course navigation menu
3. Fill in the number of days needed and provide a reason
4. Submit the request and wait for approval

### For Teachers/Managers
1. Navigate to a course where you have teaching/managing permissions
2. Click on "Manage Extensions" in the course navigation menu
3. View all extension requests for the course
4. Approve or deny pending requests

## Permissions

The plugin defines two capabilities:
- `local/extendmanualenrol:requestextension`: Allows users to request extensions (default for students)
- `local/extendmanualenrol:manageextensions`: Allows users to manage extension requests (default for teachers and managers)

## Workflow

1. Student notices their enrolment is about to expire
2. Student submits an extension request through the course menu
3. Teachers/managers receive pending requests in their management interface
4. Upon approval, the student's enrolment end date is automatically extended
5. Students can see the status of their requests

## Development

If you need to modify the plugin:
1. Update version.php when making changes
2. Add new capabilities to db/access.php if needed
3. Add new language strings to lang/en/local_extendmanualenrol.php
4. Follow Moodle coding standards and guidelines