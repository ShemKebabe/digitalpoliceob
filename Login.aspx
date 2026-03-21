<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Login.aspx.cs" Inherits="Occurence_Book_Management_System.Login" %>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title>Kenya Police Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
    <link href="Styles/Login.css" rel="stylesheet" />
</head>
<body class="d-flex align-items-center justify-content-center">
    <form id="form1" runat="server">
       <div class="login-page pt-4 pb-4">
           
            <asp:Label ID="Label4" runat="server" Text="Kenya Police Service" class="app-name text-center d-block"></asp:Label>
            <asp:Label ID="Label5" runat="server" Text="Occurence Book Management System" class="text-center d-block app-work"></asp:Label>
            <br />

            <asp:Label ID="Label1" runat="server" Text="Service Number" class="title ps-3"></asp:Label>
            <br />
            <asp:TextBox ID="txtServiceNo" runat="server" class="ms-3 input-box me-4 bg-light">
            </asp:TextBox>
            <br />
            <br />
            <asp:Label ID="Label2" runat="server" Text="Password" class="title ps-3"></asp:Label>
            <br />
            <asp:TextBox ID="txtPasswd" runat="server" class="ms-3 input-box me-4 bg-light"></asp:TextBox>
            <br />
            <br />
            <asp:Button ID="Button1" runat="server" Text="Sign in" class="ms-3 login-button me-4" />
            <br />
            <br />
            <asp:Label ID="Label3" runat="server" Text="Authorized personnel only. All Activities are logged." class="message text-center d-block">
            </asp:Label>
            <asp:HyperLink ID="HyperLink1" runat="server" class="text-center report-link d-block">
                Are you a member of the public? Report an incident here.
                <svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24"  
                    fill="#F0A800" viewBox="0 0 24 24" >
                    <!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
                    <path d="M6 13h8.09l-3.3 3.29 1.42 1.42 5.7-5.71-5.7-5.71-1.42 1.42 3.3 3.29H6z"></path>
                </svg>
            </asp:HyperLink>

        </div>
    </form>
</body>
</html>
