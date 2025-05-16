<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background-color: #f4f4f9;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
        }
        header {
            background: #0e4bf1;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        header h1 {
            font-size: 2.5rem;
        }
        section {
            margin: 20px 0;
        }
        section h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #0e4bf1;
        }
        section p {
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .team {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .team-member {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            width: 250px;
        }
        .team-member img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .team-member h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        .team-member p {
            font-size: 0.9rem;
            color: #666;
        }
        footer {
            background: #0e4bf1;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
        }
        .blog-img img {
            width: 150px;
            height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <header>
        <h1>About Our Project</h1>
    </header>
    <div class="container">
        <section>
            <h2>Our Mission</h2>
            <p>Our project aims to provide a seamless shopping experience for users by offering a user-friendly interface, robust analytics, and efficient product management tools for administrators.</p>
        </section>
        <section>
            <h2>Features</h2>
            <p>Our platform includes features such as:</p>
            <ul>
                <li>Comprehensive product management</li>
                <li>Detailed analytics dashboard</li>
                <li>User registration and management</li>
                <li>Order tracking and delivery management</li>
                <li>Dark mode for enhanced user experience</li>
            </ul>
        </section>
        <section id="blog">
            <h2>Our Blog</h2>
            <div class="blog-box">
                <div class="blog-img"><img src="b1.jpg" alt=""></div>
                <div class="blog-details">
                    <h4>Lorem, ipsum dolor.</h4>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                        Sed dignissimos exercitationem voluptatem quod earum 
                        dicta provident nam iure libero odit!</p>
                    <a href="#">CONTINUE READING</a>
                </div>
            </div>
            <div class="blog-box">
                <div class="blog-img"><img src="b2.jpg" alt=""></div>
                <div class="blog-details">
                    <h4>Lorem, ipsum dolor.</h4>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                        Sed dignissimos exercitationem voluptatem quod earum 
                        dicta provident nam iure libero odit!</p>
                    <a href="#">CONTINUE READING</a>
                </div>
            </div>
            <div class="blog-box">
                <div class="blog-img"><img src="b3.jpg" alt=""></div>
                <div class="blog-details">
                    <h4>Lorem, ipsum dolor.</h4>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                        Sed dignissimos exercitationem voluptatem quod earum 
                        dicta provident nam iure libero odit!</p>
                    <a href="#">CONTINUE READING</a>
                </div>
            </div>
            <div class="blog-box">
                <div class="blog-img"><img src="b4.jpg" alt=""></div>
                <div class="blog-details">
                    <h4>Lorem, ipsum dolor.</h4>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                        Sed dignissimos exercitationem voluptatem quod earum 
                        dicta provident nam iure libero odit!</p>
                    <a href="#">CONTINUE READING</a>
                </div>
            </div>
            <div class="blog-box">
                <div class="blog-img"><img src="b5.jpg" alt=""></div>
                <div class="blog-details">
                    <h4>Lorem, ipsum dolor.</h4>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                        Sed dignissimos exercitationem voluptatem quod earum 
                        dicta provident nam iure libero odit!</p>
                    <a href="#">CONTINUE READING</a>
                </div>
            </div>
            <div class="blog-box">
                <div class="blog-img"><img src="b6.jpg" alt=""></div>
                <div class="blog-details">
                    <h4>Lorem, ipsum dolor.</h4>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                        Sed dignissimos exercitationem voluptatem quod earum 
                        dicta provident nam iure libero odit!</p>
                    <a href="#">CONTINUE READING</a>
                </div>
            </div>
        </section>
                <section>
            <h2>Meet the Team</h2>
            <div class="team">
                <div class="team-member">
                    <img src="https://via.placeholder.com/100" alt="Team Member">
                    <h3>Siva</h3>
                    <p>Project Lead</p>
                </div>
                <div class="team-member">
                    <img src="https://via.placeholder.com/100" alt="Team Member">
                    <h3>Mesha</h3>
                    <p>Backend Developer</p>
                </div>
                <div class="team-member">
                    <img src="https://via.placeholder.com/100" alt="Team Member">
                    <h3>Nesa</h3>
                    <p>Frontend Developer</p>
                </div>
            </div> 
        </section>
        <section id="pagination" class="section-p1">
            <a href="#"></a>
            <a href="#"></a>
            <a href="#"><i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
        </section>
    </div>
    <footer>
        <p>&copy; 2025 Shopping Platform. All rights reserved.</p>
    </footer>
</body>
</html>
