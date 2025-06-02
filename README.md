# Police Traffic Management System

**Academic Project:** This was coursework completed for the *Databases, Interfaces and Software Design Principles* module as part of a Master's degree in Computer Science with Artificial Intelligence at the University of Nottingham.

## Overview

This is a web-based police traffic management system that allows police officers to record and retrieve information about vehicles, people, and traffic incidents. The system provides a comprehensive database interface for managing traffic-related data and incident reports.

## Core Features

### 1. User Authentication & Account Management
- Police officers can log in using predefined credentials
- Basic account management functionality (password changes, logout)
- Two user levels: Police Officers and Administrators

### 2. Person Search & Lookup
- Search for people by name or driving licence number
- Retrieve comprehensive person information
- Handle cases where people are not in the system

### 3. Vehicle Registration Lookup
- Search vehicles by registration (plate) numbers
- Display car details and owner information
- Show associated incidents and involved parties
- Handle missing or incomplete vehicle data

### 4. New Vehicle Registration
- Add new vehicles to the system
- Record registration number, make, model, and color
- Associate vehicles with existing or new owners
- Add new person records when owners aren't in the database

### 5. Incident Reporting & Management
- File new incident reports with detailed statements
- Record offense information and incident timing
- Associate incidents with vehicles and people
- Search and retrieve existing reports
- Edit filed reports (for the reporting officer)

### 6. Administrator Functions
- Enhanced login with administrative privileges
- Create new police officer accounts
- Associate fines with existing reports
- Access to all standard officer functions

### 7. Audit Trail System
- Track database record access and modifications
- Monitor user activities and changes
- Provide audit reports on a per-user basis
- Support regulatory and statutory requirements

## Getting Started

### Entry Point
The main entry point for the system is located at:
```
html/cw2/index.php
```

### Project Structure
All relevant project files are contained within the `html/cw2/` folder. You can safely ignore any files outside this directory as they are part of the Docker setup or other auxiliary components.

### Running the Project

#### Docker Requirements
This project requires Docker to run properly. Docker is a containerization platform that packages applications and their dependencies into lightweight, portable containers.

**What is Docker?**
Docker allows you to run applications in isolated environments called containers, ensuring consistent behavior across different systems. It eliminates "it works on my machine" problems by packaging everything needed to run the application.

**Installing Docker:**
- Download Docker Desktop: https://www.docker.com/products/docker-desktop/
- Follow the installation guide for your operating system
- Ensure Docker is running before attempting to start the project

**Using Docker with this project:**
1. Navigate to the project directory containing the Docker configuration files
2. Open terminal/command prompt in the project root directory
3. Run `docker compose up` to start the containers
4. Access the application at `http://localhost` 
5. The system will be accessible via the `html/cw2/index.php` entry point
6. Database management interface (phpMyAdmin) is available at `http://localhost:8081`

Detailed setup instructions can be found in the project's DOCKER_SETUP.md file, or visit: https://github.com/stuaart/dis-docker

## System Requirements
- Docker Desktop installed and running
- Web browser (Google Chrome recommended for testing)
- Network access to localhost

## Login Credentials

**Police Officers:**
- Username: `mcnulty` / Password: `plod123`
- Username: `moreland` / Password: `fuzz42`

**Administrator:**
- Username: `daniels` / Password: `copper99`

---

*Note: This system is designed for educational purposes. In a production environment, proper security measures including password encryption and secure authentication would be implemented.*
