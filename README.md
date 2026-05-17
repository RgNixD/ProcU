# Proc-U
## A WEB-BASED PROCUREMENT PLANNING AND CONSOLIDATION SYSTEM FOR TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES MANILA
Proc-U was built as a unified platform to streamline how TUP Manila handles procurement planning and budgeting. It connects administrative heads, budget officers, and various sectors to make the coordination and monitoring of procurement tasks much smoother. Key functions include merging PPMPs into a central APP, tracking submission statuses, and managing budget allocations. By moving away from manual work, the system ensures better transparency and accuracy through role-based access.

**Developed by**
- Ma. Margaret Agcang 
- Shaira Nicole Basulgan 
- Sofia Marie Pasco 
- Carlene Resma 
- Reinaizac Sebastian 

**Guided by**: Prof Peragrino B. Amador Jr.

## Prerequisites
Before setting up the project, ensure you have the following installed on your system:
1. **XAMPP Installer**
- Download and install XAMPP from the Official Site: [Apache Friends](https://www.apachefriends.org/)
- Ensure Apache server and MySQL database are included in the installation.
Required for the Apache server and MySQL database. 
2. **Visual Studio Code**
- Download and install [VS Code](https://code.visualstudio.com/) to manage the source code. 
- VS Code is recommended for reviewing the source code.
3. **Web Browser**
- Any modern browser (Chrome, Firefox, or Edge).
4. **Verification**
- Open the XAMPP Control Panel. 
- Click Start for both Apache and MySQL. 
- If the ports turn green, the installation is successful.

## Initial setup (First Time or After Project Clone)
Follow these steps to set up the project locally:

**Step 1: Clone the Repository**

```Bash
git clone https://github.com/RgNixD/ProcU.git 
```

**Step 2. Relocate the Project**

Move the entire proc-u folder to your XAMPP installation directory:

```bash
C:\xampp\htdocs\proc-u
```

**Step 3. Database Migration**

- Open the XAMPP Control Panel and start Apache and MySQL.

- Go to **```localhost/phpmyadmin```** in your browser.

- Create a new database named: 
**```db_procurement```**

- Click the Import tab, select the **```.sql```** file from the project's **```/database```** folder, and click Go. 

## Alternative
If XAMPP is unavailable, you can use Laragon for a more modern environment:
 
Run via ```Laragon```
1. Place project in **```C:\laragon\www\```**
2. Click [Start All]
3. Access via **```https://proc-u.test```**

## Daily workflow commands
To run the application daily:

- Open XAMPP Control Panel.

- Start Apache and MySQL.

- Navigate to **```http://localhost/procurement/```** in your browser.

## Features
- **User Role Management**: Provides specific access levels for the BAC Secretariat Head (Full CRUD), Budget Office Administrator (Financial management), and Sectors/Deans (PPMP submission).

- **Budget Allocation**: Enables the Budget Office to set and distribute annual budgets to each department while tracking used and remaining funds in real time.

- **PPMP Preparation and Submission**: Allows sectors to create procurement plans via a digital form with built-in budget validation to ensure requests stay within the allotted limit.

- **Consolidation and APP Generation**: Automatically merges all approved department plans into a single, unified Annual Procurement Plan (APP) document for easier oversight.

- **Submission Tracking**: Features a monitoring dashboard that tracks the progress of PPMP submissions and sends notifications for late or missing entries.

- **APP Revision Control**: Allows authorized administrators to update or correct consolidated plans with a full audit trail and version history to maintain transparency.

- **Standardized Report Generation**: Automatically formats and generates secure, downloadable PDF reports for both PPMP and APP requirements.

## Technologies Used
- **Frontend**: HTML5, CSS3, JavaScript

- **Backend**: PHP 

- **Database**: MySQL 

- **Server/Environment**: XAMPP (Apache) 

- **Development Tools**: VS Code, GitHub, Figma, Canva

- **Libraries & Services**: PDF Libraries (for report generation), PHPMailer (for notifications)

- **Deployment**: Hostinger

## Contributing
1. Fork the repository 
2. Create a feature branch 
3. Make your changes 
4. Test thoroughly 
5. Submit a pull request

## License 
This Proc: U capstone project for the procurement office of Technological University of the Philippines has been approved and successfully defended. It passed AI testing, similarity checks, and plagiarism verification. The project is finalized for hardbound submission, and further distribution or reuse is subject to university approval. 

## Support
For support or questions, please contact the development team or your system administrator. 