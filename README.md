# Sales Dashboard WHMCS Module

## Overview

The Sales Dashboard module provides a comprehensive and detailed dashboard for managing and analyzing sales within WHMCS. It offers visual insights through widgets and charts, displaying crucial sales-related data, including total clients, invoices, sales, and services. Additionally, the module includes a filtered data view with pagination and links to detailed client and invoice information.

## Features

- **Data Overview**: Displays key metrics such as total clients, total invoices, total sales, and total services.
- **Charts**: Visual representation of monthly data trends for clients, invoices, sales, and services.
- **Filtering**: Filter data by time period (week, month, year, custom) and invoice status.
- **Pagination**: Easily navigate through large datasets with pagination support.
- **Direct Links**: Quick access to client profiles and invoice details directly from the dashboard.

## Installation

1. **Upload Module**:
    - Upload the `sales_dashboard` directory to your WHMCS installation's `modules/addons/` directory.

2. **Activate Module**:
    - Navigate to **WHMCS Admin Area > Setup > Addon Modules**.
    - Locate `Sales Dashboard` in the list and click `Activate`.

3. **Configure Module**:
    - After activation, click on `Configure` next to the Sales Dashboard module.
    - Enable the module by selecting `Yes` under "Enable Module".

4. **Access Module**:
    - You can access the Sales Dashboard from the WHMCS admin sidebar under "Sales Dashboard".

## Usage

### Data View
- **Widgets**: Displays a summary of total clients, total invoices, total sales, and total services.
- **Table**: Provides a detailed list of invoices, with links to client profiles and invoices. You can filter the data by time period and invoice status.

### Charts View
- **Visual Insights**: Displays line charts representing monthly trends for total clients, invoices, sales, and services.

### Filtering Data
- Use the provided filters to select a time period (week, month, year, or custom range) and status to refine the displayed data.

### Pagination
- The results in the data view are paginated, allowing you to navigate through large datasets efficiently.


## Troubleshooting

- **Charts Not Displaying Correct Data**: Ensure that the data is correctly aggregated by month. The SQL queries should group the data by the month of the invoice date, client creation date, or service registration date.
- **Links Not Working**: Verify that the URLs for client profiles and invoices are correctly constructed and that the `userid` and `invoice_id` are properly passed from the database queries.

## Contributing

Contributions are welcome! Feel free to fork this repository, make your changes, and submit a pull request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

If you need help or have any questions, please feel free to reach out to the developer at [Issues](https://github.com/Nikba-Creative-Studio/Sales-Dashboard-WHMCS-Module/issues).

