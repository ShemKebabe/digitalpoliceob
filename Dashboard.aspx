<%@ Page Title="" Language="C#" MasterPageFile="~/Police.Master" AutoEventWireup="true" CodeBehind="Dashboard.aspx.cs" Inherits="Occurence_Book_Management_System.Dashboard" %>
<asp:Content ID="Content1" ContentPlaceHolderID="head" runat="server">
    <link href="Styles/Dashboard.css" rel="stylesheet" />
</asp:Content>
<asp:Content ID="Content2" ContentPlaceHolderID="ContentPlaceHolder1" runat="server">
    <div class="container-fluid mt-5">
        <div class="d-flex justify-content-between align-items-center">
            <div class="ps-4">
                <h2 class="title">Report Office</h2>
                <p class="role">
                    Duty Officer: <b>Sgt James Mwangi</b>
                </p>
            </div>


            <button class="me-4 entry-btn d-flex align-items-center">
                <svg  xmlns="http://www.w3.org/2000/svg" width="15" height="15"  
                fill="#000" viewBox="0 0 24 24" class="pe-1" >
                <!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
                <path d="M3 13h8v8h2v-8h8v-2h-8V3h-2v8H3z"></path>
                </svg>
                New OB Entry
            </button>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row  g-1 justify-content-around">
            <div class="col-sm-2 entry-container d-flex flex-column align-items-center pt-3 pe-2">
                <p>
                    <svg  xmlns="http://www.w3.org/2000/svg" width="15" height="15"  
                        fill="#FCB512" viewBox="0 0 24 24" >
                        <!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
                        <path d="M21 3h-7c-.77 0-1.47.3-2 .78-.53-.48-1.23-.78-2-.78H3c-.55 0-1 .45-1 1v15c0 .55.45 1 1 1h5.76c.53 0 1.04.21 1.41.59l1.12 1.12s.02.01.03.02c.09.08.18.15.29.2.12.05.25.08.38.08s.26-.03.38-.08c.11-.05.21-.12.29-.2 0 0 .02-.01.03-.02l1.12-1.12c.37-.37.89-.59 1.41-.59h5.76c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1ZM8.76 18H4V5h6c.55 0 1 .45 1 1v12.69c-.66-.44-1.44-.69-2.24-.69M20 18h-4.76c-.8 0-1.58.25-2.24.69V6c0-.55.45-1 1-1h6z"></path>
                    </svg>
                    Open Entries Today
                </p>
                <p class="quantity">0</p>
            </div>
            <div class="col-sm-2 entry-ctn d-flex flex-column align-items-center pt-3 pe-2">
                <p>
                    <svg  xmlns="http://www.w3.org/2000/svg" width="15" height="15"  
                    fill="#2C5E2B" viewBox="0 0 24 24" >
                    <!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
                    <path d="m19.94 7.68-.03-.09a.8.8 0 0 0-.2-.29l-5-5c-.09-.09-.19-.15-.29-.2l-.09-.03a.8.8 0 0 0-.26-.05c-.02 0-.04-.01-.06-.01H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-12s-.01-.04-.01-.06c0-.09-.02-.17-.05-.26ZM6 20V4h7v4c0 .55.45 1 1 1h4v11z"></path><path d="M8 11h8v2H8zm0 4h8v2H8zm0-8h3v2H8z"></path>
                    </svg>
                    Total Folios Used
                </p>
                <p class="quantity">3</p>
            </div>
            <div class="col-sm-2 entry-ctn d-flex flex-column align-items-center pt-3 pe-2">
                <p>
                    <svg  xmlns="http://www.w3.org/2000/svg" width="15" height="15"  
                    fill="#2C5E2B" viewBox="0 0 24 24" >
                    <!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
                    <path d="M11 11h2v6h-2zm0-4h2v2h-2z"></path><path d="M12 22c5.51 0 10-4.49 10-10S17.51 2 12 2 2 6.49 2 12s4.49 10 10 10m0-18c4.41 0 8 3.59 8 8s-3.59 8-8 8-8-3.59-8-8 3.59-8 8-8"></path>
                    </svg>
                    Pending Actions
                </p>
                <p class="quantity">1</p>
            </div>
            <div class="col-sm-2 entry-container d-flex flex-column align-items-center pt-3 pe-2">
                <p>
                    <svg  xmlns="http://www.w3.org/2000/svg" width="15" height="15"  
                    fill="#FCB512" viewBox="0 0 24 24" >
                    <!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
                    <path d="M12 2C6.58 2 2 6.58 2 12s4.58 10 10 10 10-4.58 10-10S17.42 2 12 2m0 18c-4.34 0-8-3.66-8-8s3.66-8 8-8 8 3.66 8 8-3.66 8-8 8"></path><path d="M13 7h-2v6h6v-2h-4z"></path>
                    </svg>
                    Most Recent SRS
                </p>
                <p class="quantity">OB/2026/03/001</p>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4 ms-2 me-2 px-4 entry-container">
        <h2 class="entries pt-3">Recent Entries</h2>

        <table class="table table-hover entry-table">
            <thead>
                <tr>
                    <th>SRS No.</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>OB/2026/03/001</td>
                    <td>08:30</td>
                    <td class="entry-type">Theft</td>
                    <td>Central</td>
                    <td>Open</td>
                </tr>
                <tr>
                    <td>OB/2026/03/002</td>
                    <td>10:15</td>
                    <td class="entry-type">Lost Item</td>
                    <td>Kilimani</td>
                    <td>Open</td>
                </tr>
                <tr>
                    <td>OB/2026/03/003</td>
                    <td>22:45</td>
                    <td class="entry-type">Assault</td>
                    <td>Kasarani</td>
                    <td>Under Investigation</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="container-fluid footer-section d-flex justify-content-center pt-2">
        <p>
            All Rights reserved &copy;<%= DateTime.Now.ToString("yyyy") %>. Kenya Police Service.
        </p>
    </div>
</asp:Content>
